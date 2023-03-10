<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    protected $primaryKey = 'id';
    protected $fillable = ['product_qty', 'user_id', 'product_id', 'store_id', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function store () {
        return $this->hasOne('App\Models\Store', 'id', 'store_id');
    }

    public function product () {
        return $this->belongsTo('App\Models\Product', 'product_uuid', 'product_uuid');
    }

    public function user () {
        return $this->belongsTo('App\Models\User', 'id', 'user_id');
    }

    public function getCart (Int $user) {
        return Cart::selectRaw('cart.id,
                                cart.*,
                                product.name,
                                product.net_price,
                                store.store_name,
                                store.domain')
                    ->leftJoin('product', 'product.product_uuid', '=', 'cart.product_uuid')
                    ->leftJoin('store', 'store.id', '=', 'cart.store_id')
                    ->where('cart.user_id', $user)
                    ->get();
    }

    public function getItem (String $uuid, Int $user) {
        return Cart::whereRaw('CAST(product_uuid AS TEXT) = ?', [$uuid])
                    ->where('user_id', $user)
                    ->get();
    }

    public function checkCart (Int $id, Int $user) {
        return Cart::where('id', $id)->where('user_id', $user)->get();
    }
}
