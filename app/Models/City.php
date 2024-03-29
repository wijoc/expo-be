<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'ref_city';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'ro_api_code', 'province_id'];

    public function province () {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }

    public function district () {
        return $this->hasMany('App\Models\District', 'city_id', 'id');
    }

    public function store () {
        return $this->hasMany('App/Models/Store', 'city_id', 'id');
    }

    protected function scopeFilter ($query, $filters) {
        $query->when($filters['province'] && is_array($filters['province']) ?? false, function ($query, $fProvince) {
            if (is_numeric($fProvince)) {
                $query->where('province_id', '=', $fProvince);
            }
        });

        $query->when($filters['search'] ?? false, function ($query, $keyword) {
            if (is_string($keyword)) {
                $query->orWhereRaw('LOWER(name) LIKE ?', '%'.strtolower($keyword).'%');
            } else if (is_array($keyword)) {
                foreach ($keyword as $value) {
                    $query->orWhereRaw('LOWER(name) LIKE ?', '%'.strtolower($value).'%');
                }
            }
        });
    }

    protected function scopeSorting ($query, $filters) {
        $query->when($filters['sort'] ?? false, function ($query, $s) use ($filters) {
            $query->orderBy($s, $filters['order'] ?? 'ASC');
        });
    }

    public function getCities ($filters) {
        return City::select('id', 'name', 'ro_api_code', 'province_id')
            ->filter($filters)
            ->sorting($filters)
            ->with(['province'])
            ->get();
    }

    public function findCity (Int $id) {
        return City::select('id', 'name', 'ro_api_code', 'province_id')->with(['province'])->find($id);
    }

    public function countAll ($filters = null) {
        return City::selectRaw('COUNT(id) as count_all')->filter($filters)->get();
    }

    public function checkCity (Int $id, Int $province) {
        return City::where('id', $id)->where('province_id', $province)->get();
    }
}
