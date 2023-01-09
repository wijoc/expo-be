<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'tb_user';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'email_prefix', 'verified_at', 'phone', 'phone_prefix', 'password', 'role', 'image_path'];

    public function store () {
        $this->hasOne('App\Models\Store', 'store_id', 'id');
    }

    public function getUsers () {
        return User::orderBy('id')->get();
    }

    public function countAll () {
        return User::selectRaw('COUNT(id) as count_all')->get();
    }
}
