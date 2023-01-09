<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $table = 'ref_district';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function city() {
        return $this->belongsTo('App/Models/City', 'city_id', 'id');
    }
}
