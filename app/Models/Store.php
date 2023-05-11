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
            // $query->orderByRaw('RAND()'); // For MySQL
            $query->orderByRaw('RANDOM()'); // For Postgres
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

      /** Using fromRaw or fromSub
       * This function is not written in laravel 8 docs
       * fromRaw() => just need to write raw query
       */
        // return Store::select(
        //             'store.id as store_id',
        //             'store.*',
        //             'product.id as product_id',
        //             'product.product_uuid',
        //             'product.name as product_name',
        //             'product.net_price',
        //             'product.store_id as product_store',
        //             'tz')

        //         /** fromSub */
        //         ->fromSub($fromStore->toSql(), 'store')
        //         ->mergeBindings($fromStore->getQuery())

        //         /** fromRaw */
        //         // ->fromRaw('(SELECT
        //         //             id,
        //         //             domain,
        //         //             store_name,
        //         //             image_path,
        //         //             image_mime,
        //         //             city_id,
        //         //             province_id,
        //         //             category_id
        //         //             FROM store
        //         //             WHERE store.status = \'1\'
        //         //             ORDER BY RANDOM()
        //         //             LIMIT 3) as store')

        //         ->leftJoin(DB::raw(
        //                 'LATERAL (
        //                     SELECT
        //                         id,
        //                         product_uuid,
        //                         name,
        //                         net_price,
        //                         store_id
        //                     FROM product
        //                     WHERE product.store_id = store.id
        //                     ORDER BY random() ASC
        //                     LIMIT 3
        //                 ) product'
        //             ), 'product.store_id', '=', 'store.id')
        //         ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
        //         ->with(['province', 'city'])->get();
    }

    /** Find one spesific store row, by id or domain */
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

    /** Find Multiple stores by given array of id,
     * sort by given array,
     * WITHOUT PRODUCT */
    public function findMultiStore ($ids) {
        /** CHECK ORDER BY CASE IN FUNCTION findStores() COMMENT */
        $orderCase = 'CASE store.id ';
        for ($i = 0; $i < count($ids); $i++) {
            $orderCase .= 'WHEN \''.$ids[$i].'\' THEN '.($i + 1).PHP_EOL;
        }
        $orderCase .= 'ELSE '.(count($ids) + 1).PHP_EOL.'END';

        return Store::select(
                        'store.id as store_id',
                        'store.*',
                        'tz')
                ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                ->whereIn('store.id', $ids)
                ->orderByRaw($orderCase)
                ->with(['province', 'city'])->get();
    }

    /** Find Multiple stores by given array of id,
     * sort by given array,
     * WITH MAX 3 PRODUCT EACH "store" ROW */
    public function findStores ($ids) {
        /** READ THIS BEFORE CHANGING THE CODE
         * This Query is to get store with first 3 product
         * PostgreSQL (using v12.12) didn't support := operand
         * MySQL (using mariaDB v10) didn't support limit in subquery
         *
         * Not yet test the query in another type database */

        /** If you're using PostgreSQL, USE THIS QUERY
         * the ORDER BY CASE is use to sort row in the order of the array order
         * or to mimic the result of ORDER BY FIELD() in mysql and/or mariaDB
        */
            $orderCase = 'CASE store.id ';
            for ($i = 0; $i < count($ids); $i++) {
                $orderCase .= 'WHEN \''.$ids[$i].'\' THEN '.($i + 1).PHP_EOL;
            }
            $orderCase .= 'ELSE '.(count($ids) + 1).PHP_EOL.'END';

            return Store::select(
                          'store.id as store_id',
                          'store.*',
                          'product.id as product_id',
                          'product.product_uuid',
                          'product.name as product_name',
                          'product.net_price',
                          'product.store_id as product_store',
                          'tz')
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
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
                    ->whereIn('store.id', $ids)
                    ->orderByRaw($orderCase)
                    ->with(['province', 'city'])->get();

        /** If you use MySQL or mariaDB, USE THIS QUERY
         * To Join with product, I think using join lateral will be better
         * But, there is another way around ( is only for my study notes )
         * to sort by given array in mysql, we use ORDER BY FIELD()
         */
            // $productInStore = Product::selectRaw('
            //                     prd.id,
            //                     pre.product_uuid,
            //                     pre.name,
            //                     pre.net_price,
            //                     pre.store_id,
            //                     @row_number:=CASE WHEN @store_id = store_id
            //                                     THEN @row_number + 1
            //                                     ELSE 1
            //                                 END AS rn,
            //                     @store_id := store_id')
            //                     ->from('product as prd')
            //                     ->crossJoin(DB::raw('(select @row_number := 1) as x'))
            //                     ->crossJoin(DB::raw('(select @store_id := 1) as y'))
            //                     ->orderBy('store_id', 'asc');

            // $orderField = 'id, '.implode(', ', $ids);

            // return Store::select(
            //             'store.id as store_id',
            //             'store.*',
            //             'product.id as product_id',
            //             'product.product_uuid',
            //             'product.name as product_name',
            //             'product.net_price',
            //             'product.store_id as product_store',
            //             'tz')
            //         ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
            //         ->leftJoinSub($productInStore, 'product', function ($join) {
            //             $join->on('product.store_id', '=', 'store.id')
            //                 ->where('product.rn', '<=', 3)
            //                 ->orWhere(function($query) {
            //                     $query->whereNull('product.id');
            //                 });
            //             })
            //         ->whereIn('store.id', $ids)
            //         ->orderByRaw('FIELD('.$orderField.')')
            //         ->with(['province', 'city'])->get();
    }
}
