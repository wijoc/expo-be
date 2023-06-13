<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Registration extends Model
{
    use HasFactory;

    protected $table = 'tb_registration';
    protected $primaryKey = 'id';
    protected $fillable = ['email', 'phone', 'otp', 'otp_valid_tz', 'otp_valid_until', 'verified', 'created_tz', 'created_at', 'updated_tz', 'updated_at'];

    public function findRegistration (Array $field = [], Array $identifier) {
        return Registration::select($field ?? '*')
                        ->where('email', $identifier['email'])
                        ->orWhere('phone', $identifier['phone'])
                        ->crossJoin(DB::raw('(SELECT current_setting(\'TIMEZONE\')) as tz'))
                        ->get();
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

    public function verifyOTP (String $id) {
        return Registration::where('id', $id)->update([
            'otp' => NULL,
            'otp_valid_tz' => NULL,
            'otp_valid_until' => NULL,
            'verified' => 'T',
            'updated_at' => now(),
            'updated_tz' => date_default_timezone_get()
        ]);
    }

    public function deleteRegistration (String $id) {
        return Registration::where('id', $id)->delete();
    }
}
