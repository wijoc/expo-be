<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'tb_user';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'email_prefix', 'verified_at', 'phone', 'phone_prefix', 'password', 'role', 'image_path', 'image_mime'];

    public function store () {
        $this->hasOne('App\Models\Store', 'store_id', 'id');
    }

    public function getUsers () {
        return User::orderBy('id')->get();
    }

    public function countAll () {
        return User::selectRaw('COUNT(id) as count_all')->get();
    }
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }
}
