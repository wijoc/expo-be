<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryService extends Model
{
    use HasFactory;

    protected $table = 'ref_delivery_service';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'ro_api_param'];
}
