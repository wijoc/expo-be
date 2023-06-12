<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'tb_registration';
    protected $primaryKey = 'id';
    protected $fillable = ['email', 'phone', 'otp', 'otp_valid_tz', 'otp_valid_until', 'verified', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function findRegistration (Array $identifier) {
        return Registration::where('email', $identifier['email'])->orWhere('phone', $identifier['phone'])->get();
    }

    public function inputRegistration (Array $data) {
        return Registration::updateOrCreate(
            ['email' => $data['email'], 'phone' => $data['phone']],
            [
                'otp' => $data['otp'],
                'otp_valid_until' => $data['valid_until'],
                'otp_valid_tz' => $data['valid_tz'],
                'verified' => 'N'
            ]
        );
    }
}
