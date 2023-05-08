<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $table = 'store';
    protected $primaryKey = 'id';
    protected $fillable = ['store_name', 'domain', 'email', 'phone', 'whatsapp', 'image_path', 'image_mime', 'description', 'full_address', 'user_id', 'district_id', 'city_id', 'province_id'];

    public function storeuser () {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function district () {
        return $this->belongsTo('App\Models\District', 'district_id', 'id');
    }

    public function city () {
        return $this->belongsTo('App\Models\City', 'city_id', 'id');
    }

    public function province () {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }

    public function product () {
        return $this->hasMany('App\Models\Product', 'store_id', 'id');
    }

    public function delivery () {
        return $this->hasMany('App\Models\StoreDelivery', 'id', 'store_id');
    }

    public function scopeFilter ($query, $filters) {
        $query->when($filters['search'] ?? false, function ($query, $keywords) {
            if (is_string($keywords)) {
                $query->whereRaw('LOWER(store.store_name) LIKE ?', '%'.strtolower($keywords).'%');
            } else if (is_array($keywords)) {
                foreach ($keywords as $value) {
                    $query->orWhereRaw('LOWER(store.store_name) LIKE ?', '%'.strtolower($value).'%');
                }
            }
        });

        if ($filters['province'] || $filters['city']) {
            $query->where(function ($query) use ($filters) {
                $query->when($filters['province'] ?? false, function ($query, $fProvinces) {
                    if (is_numeric($fProvinces)) {
                        $query->orWhere('store.province_id', '=', $fProvinces);
                    } else if (is_array($fProvinces)) {
                        $query->orWhereIn('store.province_id', $fProvinces);
                    }
                });

                $query->when($filters['city'] ?? false, function ($query, $fCities) {
                    if (is_numeric($fCities)) {
                        $query->orWhere('store.city_id', '=', $fCities);
                    } else if (is_array($fCities)) {
                        $query->orWhereIn('store.city_id', $fCities);
                    }
                });
            });
        }

        $query->where('store.status', 1); // Select where status is 1 = Active, 0 = Deactive
    }

    public function scopeSorting ($query, $sort) {
        $query->when($sort['sort'], function ($query, $s) use ($sort) {
            $query->orderBy($s, $sort['order']);
        }, function ($query) {
            // $query->orderByRaw('RAND() ASC'); // For MySQL
            $query->orderByRaw('RANDOM() ASC'); // For Postgres
        });
    }

    public function scopeLimitation ($query, $filter) {
        if ($filter['page'] !== 'all') {
            $query->when($filter['limit'] ?? false, function ($query, $limit) {
                $query->limit($limit);
            });

            $query->when($filter['offset'] ?? false, function ($query, $offset) {
                $query->offset($offset);
            });

            $query->when($filter['except'] && is_array($filter['except']) ?? false, function ($query, $exceptArr) {
                $query->whereNotIn('id', $exceptArr);
            });
        }
    }

    public function countAll ($filters = null) {
        return Store::selectRaw('COUNT(id) as count_all')->filter($filters)->get();
    }

    public function getAllStore ($filters) {
        return Store::select('store.id as store_id', 'store.*', 'tz')
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                    ->filter($filters)
                    ->limitation($filters)
                    ->sorting($filters)
                    ->with(['province', 'city'])->get();
    }

    public function getStores ($filters) {
      /** Using MERGEBINDING AND SUB QUERY */
        $fromStore = Store::select('id',
                                'domain',
                                'store_name',
                                'image_path',
                                'image_mime',
                                'city_id',
                                'province_id',
                                'category_id')
                            ->filter($filters)
                            ->sorting($filters)
                            ->limitation($filters);

        return Store::select(
                        'store.id as store_id',
                        'store.*',
                        'product.id as product_id',
                        'product.product_uuid',
                        'product.name as product_name',
                        'product.net_price',
                        'product.store_id as product_store',
                        'tz')
                    ->from(DB::raw("({$fromStore->toSql()}) as store"))
                    ->mergeBindings($fromStore->getQuery())
                    ->leftJoin(DB::raw(
                            'LATERAL (
                                SELECT
                                    id,
                                    product_uuid,
                                    name,
                                    net_price,
                                    store_id
                                FROM product
                                WHERE product.store_id = store.id
                                ORDER BY random() ASC
                                LIMIT 3
                            ) product'
                        ), 'product.store_id', '=', 'store.id')
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                    ->with(['province', 'city'])->get();
    }

    public function findStore ($search) {
      /**
       * This Query is to find store where id = $search or where domain = $search
       * PostgreSQL (using v12.12) didn't support comparing INT (field id is BIG INT) to a string,
       * so when then $search is a NON_Integer postgres throw an error
       * That's why i use CAST() to convert id (BIG INT) to CHAR and then compane with $search
       * Not yet test the query in another type database */

        /** If you're using MySQL, THIS QUERY WORK FINE  */
        // return Store::select('store.id as store_id', 'store.*')
        //             ->where('id', '=', $search)
        //             ->orWhere('domain', '=', $search)
        //             ->with(['province', 'city', 'district'])
        //             ->get();

        /** If you're using postgreSQL, Use this query */
        return Store::select('store.id as store_id', 'store.*')
                    ->whereRaw('CAST(store.id AS CHAR) = ?', [$search])
                    ->orWhere('domain', '=', $search)
                    ->with(['province', 'city', 'district'])
                    ->get();
    }
}
