<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;

    protected $table = 'store_category';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function store () {
        $this->hasMany('App\Models\Store', 'category_id', 'id');
    }

    protected function scopeFilter ($query, $filter) {
        $query->when($filter['search'] ?? false, function ($query, $search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
        });
    }

    public function getCategories ($filter) {
        return StoreCategory::filter($filter)->get();
    }
}
