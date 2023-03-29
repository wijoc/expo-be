<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Resources\CityResource;

class CityController extends Controller
{
    protected $cityModel;

    public function __construct()
    {
        $this->cityModel = new City();
    }

    public function index(Request $request)
    {
        $citiesData = $this->cityModel->getCities($request);
        return response()->json([
            'success' => $citiesData && count($citiesData) > 0 ? true : false,
            'message' => $citiesData && count($citiesData) > 0 ? 'Data found' : 'No Data available',
            'search' => $request->search,
            'sort_by' => $request->sort,
            'sort_order' =>$request->order,
            'count_data' => count($citiesData),
            'count_all' => $this->cityModel->countAll($request)->first()['count_all'],
            'data' => CityResource::collection($citiesData)
        ], 200);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Int $id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $city = $this->cityModel->findCity($id);
                if ($city) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Data found',
                        'data' => CityResource::make($city)
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data with ID = '.$id.' not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid path parameter. City ID must be numeric'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide path parameter (City ID)'
            ], 400);
        }
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
