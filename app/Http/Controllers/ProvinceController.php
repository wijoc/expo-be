<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;

class ProvinceController extends Controller
{
    protected $provModel;

    public function __construct () {
        $this->provModel = new Province();
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'limit' => $request->per_page ?? false,
            'offset' => $request->page > 1 ? (intval($request->page) - 1 * intval($request->per_page)) : 0,
            'order' => $request->order == 'desc' || $request->order == 'DESC' ? $request->order : 'asc'
        ];

        $provData = $this->provModel->getProvince($filters);

        return response()->json([
            'success' => $provData && count($provData) > 0 ? true : false,
            'message' => $provData && count($provData) > 0 ? 'Data found' : 'No Data available',
            'search' => $request->search,
            'page' => $request->page,
            'row_per_page' => $filters['limit'],
            'sort_order' =>$request->order,
            'count_data' => count($provData),
            'count_all' => $this->provModel->countAll($request)->first()['count_all'],
            'data' => $provData
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
                $prov = $this->provModel->findProvince($id);
                if ($prov) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Data found',
                        'data' => $prov
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
                    'message' => 'Invalid path parameter. Province ID must be numeric'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide path parameter (Province ID)'
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
