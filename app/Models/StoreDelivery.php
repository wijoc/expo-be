<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreDelivery extends Model
{
    use HasFactory;

    protected $table = 'store_delivery';
    protected $primaryKey = 'id';
    protected $fillable = ['store_id', 'delivery_courier_id', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function store () {
        return $this->belongsTo('App\Models\Store', 'id', 'store_id');
    }

    public function delivery () {
        return $this->belongsTo('App\Models\DeliveryCourier', 'id', 'delivery_courier_id');
    }

    public function getStoreDS (Int $store) {
        return StoreDelivery::where('store_id', $store)->get();
    }
}
