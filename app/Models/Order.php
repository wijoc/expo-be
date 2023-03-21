<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $table = 'trans_order';
    protected $primaryKey = 'id';
    protected $fillable = ['order_code', 'order_date', 'disc_percent', 'disc_price', 'delivery_method','delivery_service'. 'delivery_etd', 'delivery_note', 'delivery_fee', 'total_cart', 'total_payment', 'payment_method', 'payment_status', 'due_date', 'return_status', 'created_tz', 'created_at', 'updated_tz', 'updated_at', 'return_tz', 'return_at', 'store_id', 'user_id', 'user_address_id', 'delivery_courier_id'];

    public function orderuser () {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function orderaddress () {
        return $this->belongsTo('App\Models\UserAddress', 'user_address_id', 'id');
    }

    public function orderstore () {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }

    public function courier () {
        return $this->belongsTo('App\Models\DeliveryCourier', 'delivery_courier_id', 'id');
    }

    public function items () {
        return $this->hasMany('App\Models\OrderItem', 'order_code', 'order_code');
    }

    public function proofs () {
        return $this->hasMany('App\Models\PaymentProof', 'order_code', 'order_code');
    }

    protected function scopeFilter ($query, $filters) {
      /** Filter by created_at */
        // if ($filters['utc_start_date'] || $filters['utc_finish_date']) {
        //     $query->where(function ($query) use ($filters) {
        //         $query->where('created_tz', 'UTC');
        //         $query->when($filters['utc_start_date'] ?? false, function ($query, $start) {
        //             $query->where('created_at', '>=', $start);
        //         });
        //         $query->when($filters['utc_finish_date'] ?? false, function ($query, $finish) {
        //             $query->where('created_at', '<=', $finish);
        //         });
        //     });
        // }

        // if ($filters['sys_start_date'] || $filters['sys_finish_date']) {
        //     $query->orWhere(function ($query) use ($filters) {
        //         $query->where('created_tz', 'SYSTEM');
        //         $query->when($filters['sys_start_date'] ?? false, function ($query, $start) {
        //             $query->where('created_at', '>=', $start);
        //         });
        //         $query->when($filters['sys_finish_date'] ?? false, function ($query, $finish) {
        //             $query->where('created_at', '<=', $finish);
        //         });
        //     });
        // }

      /** Filter by order_date */
        if ($filters['utc_start_date'] || $filters['utc_finish_date']) {
            $query->where(function ($query) use ($filters) {
                $query->when($filters['utc_start_date'] ?? false, function ($query, $start) {
                    $query->where('order_date', '>=', $start);
                });
                $query->when($filters['utc_finish_date'] ?? false, function ($query, $finish) {
                    $query->where('order_date', '<=', $finish);
                });
            });
        }
    }

    public function getUserOrder (Array $filters, Int $user) {
        return Order::where('user_id', $user)
                    ->filter($filters)
                    ->get();
    }

    public function findOrder (String $code) {
        return Order::select('trans_order.*', 'tz')
                    ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                    ->where('order_code', $code)
                    ->with(['orderuser' => function ($query) {
                            $query->select('id', 'name');
                        },
                        'orderaddress' => function ($query) {
                            $query->select('user_address.id',
                                            'user_address.recipient_name',
                                            'user_address.full_address',
                                            'user_address.postal_code',
                                            'user_address.note',
                                            'ref_district.name as district',
                                            'ref_city.name as city',
                                            'ref_province.name as province')
                                ->leftJoin('ref_district', 'ref_district.id', '=', 'user_address.district_id')
                                ->leftJoin('ref_city', 'ref_city.id', '=', 'user_address.city_id')
                                ->leftJoin('ref_province', 'ref_province.id', '=', 'user_address.province_id');
                        },
                        'courier' => function ($query) {
                            $query->select('id', 'name');
                        },
                        'orderstore' => function ($query) {
                            $query->select('id', 'store_name', 'domain')->get();
                        }
                    ])
                    ->get();
    }

    public function insertOrder (Array $data) {
        return Order::insert($data);
    }

    public function deleteOrder (String $code) {
        return Order::where('order_code', $code)->delete();
    }
}
