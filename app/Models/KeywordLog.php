<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeywordLog extends Model
{
    use HasFactory;

    protected $table = 'client_keyword_log';
    protected $primaryKey = 'id';
    protected $fillable = ['client_ip', 'user_id', 'keyword', 'created_at', 'updated_at'];

    public function getLastKeyword($filter, $last = 1) {
        return KeywordLog::where('client_ip', $filter['client_ip'])
                            ->orWhere('user_id', $filter['user_id'])
                            ->limit($last)->get();
    }
}
