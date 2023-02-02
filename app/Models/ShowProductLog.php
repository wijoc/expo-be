<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowProductLog extends Model
{
    use HasFactory;

    protected $table = 'client_search_product_log';
    protected $primaryKey = 'id';
    protected $fillable = ['client_ip', 'user_id', 'product_id', 'page', 'keyword', 'created_at', 'updated_at'];

    protected function scopeFilter ($query, $filter) {
        $query->when($filter['user_id'] ?? false, function ($query, $id) {
            $query->orWhere('user_id', $id);
        });

        $query->when($filter['client_ip'] ?? false, function ($query, $ip) {
            $query->where('client_ip', $ip);
        });

        $query->when($filter['timelimit'] ?? false, function ($query, $timelimit) {
            $query->where('updated_at', '>', $timelimit);
        });
    }

    public function thisPageLog ($filters) {
        return ShowProductLog::where('page', $filters['page'])
                            ->where('keyword', $filters['search'])
                            ->filter($filters)->get();
    }

    public function previousLog ($filters) {
        return ShowProductLog::when($filters['page'] ?? false, function ($query, $page) {
                                if ($page !== 'all' && $page > 1) { $query->where('page', '<', $page); }
                            })
                            ->where('keyword', $filters['search'])
                            ->filter($filters)->get();
    }
}
