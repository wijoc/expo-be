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

    public function getLastKeyword($filters, $last = 1) {
        return KeywordLog::where('client_ip', $filters['client_ip'])
                            ->orWhere('user_id', $filters['user_id'])
                            ->limit($last)->get();
    }

    public function thisKeywordLog($filters) {
        return KeywordLog::where('client_ip', $filters['client_ip'])
                            ->orWhere('user_id', $filters['user_id'])
                            ->where('keyword', $filters['search'])
                            ->get();
    }
}
