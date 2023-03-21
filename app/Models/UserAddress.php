<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $table = 'user_address';
    protected $primaryKey = 'id';
    protected $fillable = ['recipient_name', 'user_id', 'district_id', 'city_id', 'province_id', 'full_address', 'postal_code', 'status', 'note', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function addressuser () {
        return $this->belongsTo('App\Models\User', 'id', 'user_id');
    }

    public function district () {
        return $this->belongsTo('App\Models\District', 'district_id', 'id');
    }

    public function city () {
        return $this->belongsTo('App\Models\City', 'city_id', 'id');
    }

    public function province () {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }

    public function getAllAddress (Int $user) {
        return UserAddress::where('user_id', $user)
                        ->with(['province', 'city', 'district'])
                        ->get();
    }

    public function getActiveAddress (Int $user) {
        return UserAddress::where('user_id', $user)
                        ->where('status', 'A')
                        ->with(['province', 'city', 'district'])
                        ->get();
    }

    public function findAddress (Int $id, Int $user) {
        return UserAddress::where('id', $id)
                            ->where('user_id', $user)
                            ->with(['province', 'city', 'district'])
                            ->get();
    }

    public function insertAddress (Array $data) {
        return UserAddress::insert($data);
    }
}
