<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use HasFactory;

    protected $table = 'store';
    protected $primaryKey = 'id';
    protected $fillable = ['store_name', 'domain', 'email', 'phone', 'whatsapp', 'image_path', 'image_mime', 'description', 'full_address', 'user_id', 'district_id', 'city_id', 'province_id'];

    public function district () {
        return $this->belongsTo('App\Models\District', 'district_id', 'id');
    }

    public function city () {
        return $this->belongsTo('App\Models\City', 'city_id', 'id');
    }

    public function province () {
        return $this->belongsTo('App\Models\Province', 'province_id', 'id');
    }

    public function product () {
        return $this->hasMany('App\Models\Product', 'store_id', 'id');
    }

    public function scopeFilter ($query, $filter) {
        $query->when($filter['search'] ?? false, function ($query, $keyword) {
            $query->where('store.store_name', 'like', '%'.$keyword.'%');
        });
    }

    public function getStores ($filters) {
      /**
       * This Query is to get store with first 3 product
       * PostgreSQL (using v12.12) didn't support := operand
       * MySQL (using mariaDB v10) didn't support limit in subquery
       * Not yet test the query in another type database */

        /** If you're using PostgreSQL, USE THIS QUERY */
        return Store::select('store.id as store_id', 'store.*', 'product.id as product_id', 'product.name as product_name', 'product.store_id as product_store')
                ->leftJoin('product', 'product.store_id', '=', 'store.id')
                ->whereIn('product.id', function ($query) {
                    $query->select('id')->from('product')->whereRaw('product.store_id = store.id')->limit(3);
                })
                ->orWhere(function ($query) {
                    $query->whereNull('product.id');
                })
                ->filter($filters)
                ->orderBy('store.id')
                ->with(['province', 'city'])->get();

        /** If you're using MySQL, USE THIS QUERY */
            // $productInStore = Product::selectRaw('prd.*,
            //                     @row_number:=CASE WHEN @store_id = store_id
            //                                     THEN @row_number + 1
            //                                     ELSE 1
            //                                 END AS rn,
            //                     @store_id := store_id')
            //                     ->from('product as prd')
            //                     ->crossJoin(DB::raw('(select @row_number := 1) as x'))
            //                     ->crossJoin(DB::raw('(select @store_id := 1) as y'))
            //                     ->orderBy('store_id', 'asc');

            // return Store::select('store.id as store_id', 'store.*', 'product.id as product_id', 'product.name as product_name', 'product.store_id as product_store')
            //         ->leftJoinSub($productInStore, 'product', function ($join) {
            //             $join->on('product.store_id', '=', 'store.id')
            //                 ->where('product.rn', '<=', 3)
            //                 ->orWhere(function($query) {
            //                     $query->whereNull('product.id');
            //                 });
            //             })
            //         ->orderBy('store.id')
            //         ->with(['province', 'city'])
            //         ->get();
    }

    public function findStore ($search) {
      /**
       * This Query is to find store where id = $search or where domain = $search
       * PostgreSQL (using v12.12) didn't support comparing INT (field id is BIG INT) to a string,
       * so when then $search is a NON_Integer postgres throw an error
       * That's why i use CAST() to convert id (BIG INT) to CHAR and then compane with $search
       * Not yet test the query in another type database */

        /** If you're using MySQL, THIS QUERY WORK FINE  */
        // return Store::select('store.id as store_id', 'store.*')
        //             ->where('id', '=', $search)
        //             ->orWhere('domain', '=', $search)
        //             ->with(['province', 'city', 'district'])
        //             ->get();

        /** If you're using postgreSQL, Use this query */
        return Store::select('store.id as store_id', 'store.*')
                    ->whereRaw('CAST(store.id AS CHAR) = ?', [$search])
                    ->orWhere('domain', '=', $search)
                    ->with(['province', 'city', 'district'])
                    ->get();
    }

    // public function insertStore ($request) {
    //     $this->
    // }
}
