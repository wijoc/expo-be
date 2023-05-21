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

    public function cartuser () {
        return $this->belongsTo('App\Models\User', 'id', 'user_id');
    }

    public function getCart (Int $user, Array $filter = []) {
        return Cart::selectRaw('cart.id,
                                cart.*,
                                product.id as product_id,
                                product.product_uuid,
                                product.name,
                                product.initial_price,
                                product.net_price,
                                product.disc_percent,
                                product.disc_price,
                                product.weight_g,
                                store.store_name,
                                store.domain')
                    ->leftJoin('product', 'product.product_uuid', '=', 'cart.product_uuid')
                    ->leftJoin('store', 'store.id', '=', 'cart.store_id')
                    ->where('cart.user_id', $user)
                    ->orderBy('cart.created_at', 'desc')
                    ->get();
    }

    public function selectCarts (Int $user, Array $whereIn) {
        return Cart::selectRaw('cart.id,
                                cart.*,
                                product.id as product_id,
                                product.product_uuid,
                                product.name,
                                product.initial_price,
                                product.net_price,
                                product.disc_percent,
                                product.disc_price,
                                product.weight_g,
                                store.store_name,
                                store.domain')
                    ->leftJoin('product', 'product.product_uuid', '=', 'cart.product_uuid')
                    ->leftJoin('store', 'store.id', '=', 'cart.store_id')
                    ->where('cart.user_id', $user)
                    ->whereIn('cart.id', $whereIn)
                    ->orderBy('cart.created_at', 'desc')
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

    public function deleteCart ($ids) {
        return Cart::whereIn('id', $ids)->delete();
    }
}
