<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_img';
    protected $primaryKey = 'id';
    protected $fillable = ['product_uuid', 'mime', 'path', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function product () {
        return $this->belongsTo('App\Models\Product', 'product_uuid', 'product_uuid');
    }
}
