<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Resources\CityResource;

class CityController extends Controller
{
    public function __construct()
    {
        $this->cityModel = new City();
    }

    public function index(Request $request)
    {
        $citiesData = $this->cityModel->getCities($request);
        if ($citiesData && count($citiesData) > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Data found',
                'search' => $request->search,
                'sort_by' => $request->sort,
                'sort_order' =>$request->order,
                'count_data' => count($citiesData),
                'count_all' => $this->cityModel->countAll($request)->first()['count_all'],
                'data' => CityResource::collection($citiesData)
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data available',
                'search' => $request->search,
                'sort_by' => $request->sort,
                'sort_order' =>$request->order,
                'count_data' => 0,
                'count_all' => $this->cityModel->countAll($request)->first()['count_all'],
                'data' => null
            ], 200);
        }
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
