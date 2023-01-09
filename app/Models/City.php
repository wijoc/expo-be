<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'ref_city';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function province() {
        return $this->belongsTo('App/Models/Province', 'province_id', 'id');
    }

    public function district() {
        return $this->hasMany('App/Models/District', 'city_id', 'id');
    }

    public function store() {
        return $this->hasMany('App/Models/Store', 'city_id', 'id');
    }
}
