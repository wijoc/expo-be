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
    protected $fillable = ['name', 'condition', 'initial_price', 'net_price', 'disc_percent', 'disc_price', 'weight_g', 'min_purchase', 'store_id', 'category_id'];

    public function store () {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }

    public function category () {
        return $this->belongsTo('App\Models\ProductCategory', 'category_id', 'id');
    }

    protected function scopeFilter ($query, $filter) {
        $query->when($filter['search'] ?? false, function ($query, $keyword) {
            $query->where('name', 'like', '%'.$keyword.'%');
        });

        $query->when($filter['condition'] ?? false, function ($query, $condition) {
            if ($condition == 'new') {
                $query->where('condition', '=', 'N');
            } else if ($condition == 'secondhand') {
                $query->where('condition', 'SH');
            }
        });

        $query->when($filter['min_price'] ?? false, function ($query, $minPrice) {
            $query->where('net_price', '>=', $minPrice);
        });

        $query->when($filter['max_price'] ?? false, function ($query, $maxPrice) {
            $query->where('net_price', '<=', $maxPrice);
        });
    }

    public function countAll () {
        return Product::selectRaw('COUNT(id) as count_all')->get();
    }

    public function getProducts ($filter) {
        $category = DB::table('product_category')
                        ->select('c.*', 'p.name as parent_name')
                        ->from('product_category as c')
                        ->leftJoin('product_category as p', 'p.id', '=', 'c.parent_id');

        return Product::select(
                        'product.id',
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
                        'category.name as category_name',
                        'category.is_sub_category',
                        'category.parent_id as category_parent_id',
                        'category.parent_name as category_parent_name',
                        'ref_city.name as city_name',
                        'ref_city.ro_api_code as city_ro_code',
                        'ref_province.name as province_name')
                    ->leftJoin('store', 'store.id', '=', 'product.store_id')
                    ->leftJoin('ref_city', 'store.city_id', '=', 'ref_city.id')
                    ->leftJoin('ref_province', 'store.province_id', '=', 'ref_province.id')
                    ->leftJoinSub($category, 'category', function ($join) {
                        $join->on('category.id', '=', 'product.category_id');
                    })
                    ->filter($filter)
                    ->get();
    }

    public function findProduct ($id) {
        $category = DB::table('product_category')
                        ->select('c.*', 'p.name as parent_name')
                        ->from('product_category as c')
                        ->leftJoin('product_category as p', 'p.id', '=', 'c.parent_id');

        return Product::select(
                        'product.id',
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
                        'category.name as category_name',
                        'category.is_sub_category',
                        'category.parent_id as category_parent_id',
                        'category.parent_name as category_parent_name',
                        'ref_city.name as city_name',
                        'ref_city.ro_api_code as city_ro_code',
                        'ref_province.name as province_name')
                    ->leftJoin('store', 'store.id', '=', 'product.store_id')
                    ->leftJoin('ref_city', 'store.city_id', '=', 'ref_city.id')
                    ->leftJoin('ref_province', 'store.province_id', '=', 'ref_province.id')
                    ->leftJoinSub($category, 'category', function ($join) {
                        $join->on('category.id', '=', 'product.category_id');
                    })
                    ->where('product.id', $id)
                    ->get();
    }
}
