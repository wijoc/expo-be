<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\StoreCategory;

class StoreCategoryController extends Controller
{
    public function __construct () {
        $this->categoryModel = new StoreCategory();
        $this->rules = [
            'name' => 'required|max:50|unique:App\Models\StoreCategory'
        ];
        $this->messages = [
            'name.required' => 'Name is required',
            'name.max' => 'Name can not be more than 50 character',
            'name.unique' => 'Category already exist'
        ];
    }

    public function index(Request $request)
    {
        $rawData = $this->categoryModel->getCategories($request);

        if ($rawData && count($rawData) > 0) {
            $categories = [];
            foreach ($rawData as $key => $value) {
                $categories[$value['id']] = [
                    'id' => $value['id'],
                    'name' => $value['name']
                ];
            }

            return response()->json([
                'success' => true,
                'error' => false,
                'count_data' => count(array_values($categories)),
                // 'count_all' => ,
                'data' => array_values($categories)
            ], 200);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No data available!'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $validated = [
                'name' => $validator->validated()['name']
            ];

            $inputCategory = StoreCategory::insert($validated);

            if ($inputCategory) {
                return response()->json([
                    'success' => true,
                    'error' => false,
                    'message' => 'Success add new data'
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => false,
                    'message' => 'Failed add new data'
                ], 500);
            }
        }
    }

    public function show($id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $category = StoreCategory::find($id);

                if ($category) {
                    $data = [
                        'id' => $category['id'],
                        'name' => $category['name']
                    ];

                    return response()->json([
                        'success' => true,
                        'message' => 'Data found',
                        'data' => $data
                    ], 404);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Data not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Parameter is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide path parameter ID'
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $this->rules['name'] = 'required|max:50|unique:App\Models\StoreCategory,name,'.$id;

                $validator = Validator::make($request->all(), $this->rules, $this->messages);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => true,
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    $validated = [
                        'name' => $validator->validated()['name']
                    ];

                    $updateCategory = StoreCategory::where('id', $id)->update($validated);

                    if ($updateCategory) {
                        return response()->json([
                            'success' => true,
                            'error' => false,
                            'message' => 'Data updated successfully'
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'error' => false,
                            'message' => 'Failed to update data'
                        ], 500);
                    }
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Parameter is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide path parameter ID'
            ], 400);
        }
    }

    public function destroy($id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $deleteCategory = StoreCategory::where('id', $id)->delete();

                if ($deleteCategory) {
                    return response()->json([
                        'success' => true,
                        'error' => false,
                        'message' => 'Data deleted'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => false,
                        'message' => 'Failed to delete data'
                    ], 500);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Parameter is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide path parameter ID'
            ], 400);
        }
    }
}
