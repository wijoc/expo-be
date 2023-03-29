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

    protected function scopeFilter ($query, $filters) {
        $query->when($filters->search ?? false, function ($query, $keyword) {
            $query->where('name', 'like', '%'.$keyword.'%');
        });

        $query->when($filters->limit ?? false, function ($query, $lim) {
            $query->limit($lim);
        });

        $query->when($filters->offset ?? false, function ($query, $os) {
            $query->limit($os);
        });

        $query->when($filters->order ?? false, function ($query, $so) {
            $query->orderBy('name', $so);
        });

    }

    public function countAll ($filters) {
        return Province::selectRaw('COUNT(id) as count_all')->filter($filters)->get();
    }

    public function getProvince ($filters) {
        return Province::select('id', 'name')
                        ->filter($filters)
                        ->get();
    }

    public function findProvince (Int $id) {
        return Province::select('id', 'name')->find($id);
    }
}
