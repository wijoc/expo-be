<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Home extends Model {
    // use HasFactory;

    protected $table = 'tb_home';
    protected $primaryKey = 'id';
    protected $fillable = ['banner', 'about', 'phone', 'email', 'address'];
}
