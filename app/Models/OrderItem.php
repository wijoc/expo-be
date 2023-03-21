<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_item';
    protected $primaryKey = 'id';
    protected $fillable = ['qty', 'initial_price', 'net_price', 'disc_percent', 'disc_price', 'created_tz', 'created_at', 'updated_tz', 'updated_at', 'order_code', 'product_uuid'];

    public function transorder () {
        return $this->belongsTo('App\Models\Order', 'order_code', 'order_code');
    }

    public function insertItem (Array $data) {
        return OrderItem::insert($data);
    }
}
