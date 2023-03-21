<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeliveryCourier extends Model
{
    use HasFactory;

    protected $table = 'ref_delivery_courier';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'ro_api_param'];

    public function storedelivery () {
        return $this->hasMany('App\Models\StoreDelivery', 'id', 'delivery_courier_id');
    }

    protected function scopeFilter ($query, $filters) {
        $query->when($filters['search'] ?? false, function ($query, $keyword) {
            $query->where('name', 'like', '%'.$keyword.'%');
        });
    }

    public function getCouriers ($filters) {
        return DeliveryCourier::select('ref_delivery_courier.*', 'tz')
                            ->filter($filters)
                            ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                            ->get();
    }

    public function findCouriers ($id) {
        return DeliveryCourier::select('ref_delivery_courier.*', 'tz')
                            ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                            ->where('id', $id)
                            ->get();
    }
}
