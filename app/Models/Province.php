<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Province extends Model
{
    use HasFactory;

    protected $table = 'ref_province';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];

    public function city() {
        return $this->hasMany('App/Models/City', 'province_id', 'id');
    }

    protected function scopeFilter ($query, $filter) {
        $query->when($filter->search ?? false, function ($query, $keyword) {
            $query->where('name', 'like', '%'.$keyword.'%');
        });
    }

    protected function scopeLimitation ($query, $filter) {
        $query->when($filter->limit ?? false, function ($query, $lim) {
            $query->limit($lim);
        });

        $query->when($filter->offset ?? false, function ($query, $os) {
            $query->limit($os);
        });
    }

    protected function scopeSorting ($query, $filter) {
        $query->when($filter->order ?? false, function ($query, $so) {
            $query->orderBy('name', $so);
        });
    }

    public function countAll ($filters) {
        return Province::selectRaw('COUNT(id) as count_all')->filter($filters)->get();
    }

    public function getProvince ($filters) {
        return Province::select('id', 'name')
                        ->filter($filters)
                        ->limitation($filters)
                        ->sorting($filters)
                        ->get();
    }

    public function findProvince (Int $id) {
        return Province::select('id', 'name')->find($id);
    }
}
