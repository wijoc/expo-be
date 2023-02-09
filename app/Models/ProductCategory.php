<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_category';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'is_sub_category', 'parent_id'];

    public function product () {
        $this->hasMany('App\Models\Product', 'category_id', 'id');
    }

    protected function scopeFilter ($query, $filter) {
        $query->when($filter['search'] ?? false, function ($query, $search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
        });

        $query->when($filter['parent'] ?? false, function ($query, $parent) {
            if (is_numeric($parent)) {
                $query->where('parent_id', $parent);
            }
        });
    }

    public function getCategories ($filter) {
        return ProductCategory::filter($filter)->get();
    }

    public function findCategory ($id) {
        return ProductCategory::select('c.*', 'p.name as parent_name', 'p.id as parent_id')
                                ->from('product_category as c')
                                ->leftJoin('product_category as p', 'p.id', '=', 'c.parent_id')
                                ->where('c.id', $id)
                                ->get();
    }
}
