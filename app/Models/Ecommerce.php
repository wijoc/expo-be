<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ecommerce extends Model
{
    use HasFactory;

    protected $table = 'ref_ecommerce';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];
}
