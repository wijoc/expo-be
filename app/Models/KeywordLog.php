<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeywordLog extends Model
{
    use HasFactory;

    protected $table = 'client_keyword_log';
    protected $primaryKey = 'id';
    protected $fillable = ['keyword', 'search_count', 'created_at', 'updated_at'];

    public function getKeywordLog ($clientIP = null, $userID = null, $keyword) {
        return KeywordLog::where(function ($query) use ($clientIP, $userID) {
                                $query->when($clientIP ?? false, function ($query, $ip) {
                                    $query->where('client_ip', $ip);
                                })
                                ->when($userID ?? false, function ($query, $user) {
                                    $query->orWhere('user_id', $user);
                                });
                            })
                            ->whereRaw('LOWER(keyword) = ?', strtolower($keyword))->get();
    }

    public function writeLogKeyword ($data) {
        return KeywordLog::insert($data);
    }

    public function updateLogKeyword ($keyword, $data) {
        return KeywordLog::where('keyword', $keyword)->update($data);
    }
}
