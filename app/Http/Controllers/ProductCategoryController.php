<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function __construct () {
        $this->categoryModel = new ProductCategory();
        $this->rules = [
            'name' => 'required|max:50|unique:App\Models\ProductCategory',
            'is_sub_category' => 'required|boolean',
            'parent_id' => 'required_if:is_sub_category,true|nullable|numeric'
        ];
        $this->messages = [
            'name.required' => 'Name is required',
            'name.max' => 'Name can not be more than 50 character',
            'name.unique' => 'Category already exist',
            'is_sub_category.required' => 'is_sub_category is required',
            'is_sub_category.boolean' => 'value must be boolean (true, false, 0, 1, "0" or "1")',
            'parent_id.required_if' => 'Parent ID is required'
        ];
    }

    public function index(Request $request)
    {
        $rawData = $this->categoryModel->getCategories($request);

        if ($rawData && count($rawData) > 0) {
            $categories = [];
            foreach ($rawData as $key => $value) {
                if ($value['is_sub_category'] === 0) {
                    if (!array_key_exists($value['id'], $categories)) {
                        $categories[$value['id']] = [
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'is_sub_category' => ($value['is_sub_category'] == 0 ? False : True),
                            'parent_id' => $value['parent_id']
                        ];
                        $categories[$value['id']]['sub_category'] = [];
                    } else {
                        $categories[$value['id']] = [
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'is_sub_category' => ($value['is_sub_category'] == 0 ? False : True),
                            'parent_id' => $value['parent_id']
                        ];
                    }
                } else {
                    if (!array_key_exists($value['parent_id'], $categories)) {
                        $categories[$value['parent_id']]['sub_category'] = [];
                        $child = $categories[$value['parent_id']]['sub_category'];
                        array_push($child, $value);
                        $categories[$value['parent_id']]['sub_category'] = $child;
                    } else {
                        $child = $categories[$value['parent_id']]['sub_category'];
                        array_push($child, $value);
                        $categories[$value['parent_id']]['sub_category'] = $child;
                    }
                }
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
            if ($validator->validated()['is_sub_category']){
                $validateParent = ProductCategory::find($validator->validated()['parent_id']);
            }

            if ($validateParent) {
                $validated = [
                    'name' => $validator->validated()['name'],
                    'is_sub_category' => (int)filter_var($validator->validated()['is_sub_category'], FILTER_VALIDATE_BOOLEAN),
                    'parent_id' => $validator->validated()['is_sub_category'] ? $validator->validated()['parent_id'] : null,
                ];

                $inputCategory = ProductCategory::insert($validated);

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
            } else {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'The given data was invalid',
                    'errors' => [
                        'parent_id' => [
                            'Parent ID not found'
                        ]
                    ]
                ], 400);
            }
        }
    }

    public function show($id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $category = $this->categoryModel->findCategory($id);
                $data = [
                    'id' => $category[0]['id'],
                    'name' => $category[0]['name'],
                    'is_sub_category' => $category[0]['is_sub_category'],
                    'parent_id' => $category[0]['parent_id'],
                    'parent_name' => $category[0]['parent_name'],
                ];

                if ($category && count($category) > 0) {
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
                $this->rules['name'] = 'required|max:50|unique:App\Models\ProductCategory,name,'.$id;

                $validator = Validator::make($request->all(), $this->rules, $this->messages);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => true,
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    if ($validator->validated()['is_sub_category']){
                        $validateParent = ProductCategory::find($validator->validated()['parent_id']);
                    }

                    if ($validateParent) {
                        $validated = [
                            'name' => $validator->validated()['name'],
                            'is_sub_category' => (int)filter_var($validator->validated()['is_sub_category'], FILTER_VALIDATE_BOOLEAN),
                            'parent_id' => $validator->validated()['is_sub_category'] ? $validator->validated()['parent_id'] : null,
                        ];

                        $updateCategory = ProductCategory::where('id', $id)->update($validated);

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
                    } else {
                        return response()->json([
                            'success' => false,
                            'error' => true,
                            'message' => 'The given data was invalid',
                            'errors' => [
                                'parent_id' => [
                                    'Parent ID not found'
                                ]
                            ]
                        ], 400);
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
                $deleteCategory = ProductCategory::where('id', $id)->delete();

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
