<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowStoreLog extends Model
{
    use HasFactory;

    protected $table = 'client_search_store_log';
    protected $primaryKey = 'id';
    protected $fillable = ['client_ip', 'user_id', 'store_id', 'page', 'keyword', 'created_at', 'updated_at'];

    protected function scopeFilter($query, $filter) {
        $query->when($filter['user_id'] ?? false, function ($query, $id) {
            $query->orWhere('user_id', $id);
        });

        $query->when($filter['client_ip'] ?? false, function ($query, $ip) {
            $query->where('client_ip', $ip);
        });

        // $query->when($filter['page'] ?? false, function ($query, $page) {
        //     $query->where('page', $page);
        // });

        $query->when($filter['timelimit'] ?? false, function ($query, $timelimit) {
            $query->where('updated_at', '>', $timelimit);
        });
    }

    public function thisPageLog ($filters) {
        return ShowStoreLog::where('page', $filters['page'])
                            ->where('keyword', $filters['search'])
                            ->filter($filters)->get();
    }

    public function previousLog ($filters) {
        return ShowStoreLog::when($filters['page'] ?? false, function ($query, $page) {
                                if ($page !== 'all' && $page > 1) {
                                    $query->where('page', '<', $page);
                                } else if ($page !== 'all' && $page == 1){
                                    $query->where('page', '<>', $page);
                                }
                            })
                            ->where('keyword', $filters['search'])
                            // ->where('updated_at', '>', $filters['timelimit'])
                            ->filter($filters)->get();
    }
}
