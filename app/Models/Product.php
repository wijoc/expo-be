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
    protected $fillable = ['name', 'condition', 'initial_price', 'net_price', 'disc_percent', 'disc_price', 'weight_g', 'min_purchase', 'store_id', 'category_id', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function store () {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }

    public function category () {
        return $this->belongsTo('App\Models\ProductCategory', 'category_id', 'id');
    }

    public function image () {
        return $this->hasMany('App\Models\ProductImage', 'product_uuid', 'product_uuid');
    }

    protected function scopeFilter ($query, $filters) {
        $query->when($filters['search'] ?? false, function ($query, $keyword) {
            $query->where('product.name', 'like', '%'.$keyword.'%');
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
                    ->with('image')
                    ->get();
    }

    public function findProduct ($id) {
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

    public function checkProductStore ($id, $store) {
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
}
