<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'ref_province';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function city() {
        return $this->hasMany('App/Models/City', 'province_id', 'id');
    }
}
