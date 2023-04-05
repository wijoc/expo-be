<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';
    protected $primaryKey = 'id';
    protected $fillable = ['product_uuid', 'name', 'condition', 'initial_price', 'net_price', 'disc_percent', 'disc_price', 'weight_g', 'min_purchase', 'store_id', 'category_id', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function store () {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }

    public function category () {
        return $this->belongsTo('App\Models\ProductCategory', 'category_id', 'id');
    }

    public function image () {
        return $this->hasMany('App\Models\ProductImage', 'product_uuid', 'product_uuid');
    }

    public function cart () {
        return $this->hasMany('App\Models\Cart', 'product_uuid', 'product_uuid');
    }

    protected function scopeFilter ($query, $filters) {
        $query->when($filters['search'] ?? false, function ($query, $keywords) {
            if (is_string($keywords)) {
                $query->whereRaw('LOWER(product.name) LIKE ?', '%'.strtolower($keywords).'%');
            } else if (is_array($keywords)) {
                foreach ($keywords as $value) {
                    $query->orWhereRaw('LOWER(product.name) LIKE ?', '%'.strtolower($value).'%');
                }
            }
        });

        $query->when($filters['condition'] ?? false, function ($query, $condition) {
            if (strtolower($condition) == 'new') {
                $query->where('condition', '=', 'N');
            } else if (strtolower($condition) == 'secondhand') {
                $query->where('condition', 'SH');
            }
        });

        $query->when($filters['min_price'] ?? false, function ($query, $minPrice) {
            $query->where('net_price', '>=', $minPrice);
        });

        $query->when($filters['max_price'] ?? false, function ($query, $maxPrice) {
            $query->where('net_price', '<=', $maxPrice);
        });

        $query->when($filters['store'] ?? false, function ($query, $store) {
            $query->where('store_id', $store);
        });

        if (isset($filters['province']) || isset($filters['city'])) {
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
    }

    protected function scopeMultisearch ($query, $filters) {
        $query->when($filters ?? false, function ($query, $keywords) {
            // $query->where('product.name', 'like', '%%')
            foreach($keywords as $keyword) {
                $query->orWhere('product.name', 'LIKE', '%'.$keyword.'%');
            }
        });
    }

    protected function scopeSorting ($query, $sort) {
        $query->when($sort['sort'], function ($query, $s) use ($sort) {
            $query->orderBy($s, $sort['order']);
        }, function ($query) {
            // $query->orderByRaw('RAND() ASC'); // For MySQL
            $query->orderByRaw('RANDOM() ASC'); // For Postgres
        });
    }

    protected function scopeLimitation ($query, $filter) {
        if ($filter['page'] !== 'all') {
            $query->when($filter['limit'] ?? false, function ($query, $limit) {
                $query->limit($limit);
            });

            $query->when($filter['offset'] ?? false, function ($query, $offset) {
                $query->offset($offset);
            });
        }
    }

    public function countAll ($filters = null) {
        return Product::selectRaw('COUNT(id) as count_all')->filter($filters)->get();
    }

    public function getProducts ($filters) {
        $category = DB::table('product_category')
                        ->select('c.*', 'p.name as parent_name')
                        ->from('product_category as c')
                        ->leftJoin('product_category as p', 'p.id', '=', 'c.parent_id');

        return Product::select(
                        'product.id',
                        'product.product_uuid',
                        'product.name as product_name',
                        'condition',
                        'initial_price',
                        'net_price',
                        'disc_percent',
                        'disc_price',
                        'weight_g',
                        'min_purchase',
                        'store_id',
                        'product.category_id',
                        'product.created_at',
                        'product.created_tz',
                        'product.updated_at',
                        'product.updated_tz',
                        'category.name as category_name',
                        'category.is_sub_category',
                        'category.parent_id as category_parent_id',
                        'category.parent_name as category_parent_name',
                        'ref_city.name as city_name',
                        'ref_city.ro_api_code as city_ro_code',
                        'ref_province.name as province_name',
                        'store.store_name',
                        'store.domain',
                        'tz')
                    ->leftJoin('store', 'store.id', '=', 'product.store_id')
                    ->leftJoin('ref_city', 'store.city_id', '=', 'ref_city.id')
                    ->leftJoin('ref_province', 'store.province_id', '=', 'ref_province.id')
                    ->leftJoinSub($category, 'category', function ($join) {
                        $join->on('category.id', '=', 'product.category_id');
                    })
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                    ->filter($filters)
                    ->sorting($filters)
                    ->limitation($filters)
                    ->with('image')
                    ->get();
    }

    public function findProduct (String $id) {
        $category = DB::table('product_category')
                        ->select('c.*', 'p.name as parent_name')
                        ->from('product_category as c')
                        ->leftJoin('product_category as p', 'p.id', '=', 'c.parent_id');

        return Product::select(
                        'product.id',
                        'product.product_uuid',
                        'product.name as product_name',
                        'condition',
                        'initial_price',
                        'net_price',
                        'disc_percent',
                        'disc_price',
                        'weight_g',
                        'min_purchase',
                        'store_id',
                        'product.category_id',
                        'product.created_at',
                        'product.created_tz',
                        'product.updated_at',
                        'product.updated_tz',
                        'category.name as category_name',
                        'category.is_sub_category',
                        'category.parent_id as category_parent_id',
                        'category.parent_name as category_parent_name',
                        'ref_city.name as city_name',
                        'ref_city.ro_api_code as city_ro_code',
                        'ref_province.name as province_name',
                        'store.store_name',
                        'store.domain',
                        'tz')
                    ->leftJoin('store', 'store.id', '=', 'product.store_id')
                    ->leftJoin('ref_city', 'store.city_id', '=', 'ref_city.id')
                    ->leftJoin('ref_province', 'store.province_id', '=', 'ref_province.id')
                    ->leftJoinSub($category, 'category', function ($join) {
                        $join->on('category.id', '=', 'product.category_id');
                    })
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))

                    /** Use this for PostgreSQL */
                    ->whereRaw('CAST(product.id AS CHAR) = ?', [$id])
                    ->orWhereRaw('CAST(product.product_uuid AS TEXT) = ?', [$id])

                    /** Use this for MySQL */
                    // ->where('product.id', $id)
                    // ->orWhere('product.product_uuid', $id)

                    ->get();
    }

    public function checkProductStore (String $id, Int $store) {
        return Product::select('id', 'product_uuid')
                ->where('store_id', $store)
                /** Use this for PostgreSQL */
                ->whereRaw('CAST(product.id AS CHAR) = ?', [$id])
                ->orWhereRaw('CAST(product.product_uuid AS TEXT) = ?', [$id])

                /** Use this for MySQL */
                // ->where('product.id', $id)
                // ->orWhere('product.product_uuid', $id)
                ->get();
    }

    public function countAllSimilar (Array $keywords, Array $categories) {
        return Product::selectRaw('COUNT(id) as count_all')
                    ->multisearch($keywords)
                    ->whereIn('product.category_id', $categories)
                    ->get();

    }

    public function similarProduct (Array $keywords, Array $categories) {
        $category = DB::table('product_category')
                        ->select('c.*', 'p.name as parent_name')
                        ->from('product_category as c')
                        ->leftJoin('product_category as p', 'p.id', '=', 'c.parent_id');

        return Product::select(
                        'product.id',
                        'product.product_uuid',
                        'product.name as product_name',
                        'condition',
                        'initial_price',
                        'net_price',
                        'disc_percent',
                        'disc_price',
                        'weight_g',
                        'min_purchase',
                        'store_id',
                        'product.category_id',
                        'product.created_at',
                        'product.created_tz',
                        'product.updated_at',
                        'product.updated_tz',
                        'category.name as category_name',
                        'category.is_sub_category',
                        'category.parent_id as category_parent_id',
                        'category.parent_name as category_parent_name',
                        'ref_city.name as city_name',
                        'ref_city.ro_api_code as city_ro_code',
                        'ref_province.name as province_name',
                        'store.store_name',
                        'store.domain',
                        'tz')
                    ->leftJoin('store', 'store.id', '=', 'product.store_id')
                    ->leftJoin('ref_city', 'store.city_id', '=', 'ref_city.id')
                    ->leftJoin('ref_province', 'store.province_id', '=', 'ref_province.id')
                    ->leftJoinSub($category, 'category', function ($join) {
                        $join->on('category.id', '=', 'product.category_id');
                    })
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                    ->multisearch($keywords)
                    ->whereIn('product.category_id', $categories)
                    ->get();
    }
}
