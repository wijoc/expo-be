<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;

class ProductController extends Controller
{
    public function __construct(){
        $this->product = new Product();
        $this->rules = [
            'name' => 'required|min:1|max:60',
            'condition' => ['required', Rule::in(["N","SH"])],
            'initial_price' => 'required|numeric|between:0,9999999999999.99',
            'discount_percent' => 'present|nullable|numeric|between:0,100.00',
            'discount_price' => 'present|nullable|numeric',
            'weight_in_gram' => 'required|numeric',
            'min_purchase' => 'required|numeric',
            'category_id' => 'required',
        ];
        $this->messages = [
            'name.required' => 'Product name is required',
            'name.min' => 'Product name must be at least 1 character',
            'name.max' => 'Product name can not be more than 60 character',
            'condition.required' => 'Product condition is required',
            'condition.in' => 'Value must be "N" or "SH"',
            'initial_price.required' => 'Initial price is required',
            'initial_price.numeric' => 'Value must be numeric',
            'discount_percent.present' => 'Discount percent must be present but can be empty',
            'discount_percent.numeric' => 'Value must be numeric',
            'discount_price.present' => 'Discount price must be present but can be empty',
            'discount_price.numeric' => 'Value must be numeric',
            'weight_in_gram.required' => 'Weight is required',
            'weight_in_gram.numeric' => 'Value must be numeric',
            'min_purchase.required' => 'Min Purchase is required',
            'min_purchase.numeric' => 'Value must be numeric',
            'category_id' => 'Category ID is required',
        ];
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'min_price' => 'nullable|numeric',
                'max_price' => 'nullable|numeric',
                'condition' => [Rule::in(["new", "second"])]
            ],
            [
                'min_price.numeric' => 'Invalid value. Value must be numeric',
                'max_price.numeric' => 'Invalid value. Value must be numeric',
                'condition' => 'Invalid value. Value must be "new" or "secondhand"'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Parameter was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $products = $this->product->getProducts($request);

            return response()->json([
                'success' => true,
                'count_data' => count($products),
                'count_all' => $this->product->countAll()[0]->count_all,
                'data' => ProductResource::collection($products)
            ], 200);
        }
    }

    public function store(Request $request)
    {
        $this->rules['store_id'] = 'required';
        $this->messages['store_id'] = 'Store ID is required';

        $validator = Validator::make($request->all(), $this->rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $validateCategory = ProductCategory::find($validator->validated()['category_id']);
            $validateStore = Store::find($validator->validated()['store_id']);

            if ($validateCategory === null || $validateStore === null) {
                $validateStore === null ? $errors['store_id'] = 'Store ID not found' : '';
                $validateCategory === null ? $errors['category_id'] = 'Category ID not found' : '';

                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'The given data was invalid',
                    'errors' => $errors
                ], 400);
            } else {
                $inputData = [
                    'name' => $validator->validated()['name'],
                    'condition' => $validator->validated()['condition'],
                    'initial_price' => $validator->validated()['initial_price'],
                    'disc_percent' => $validator->validated()['discount_percent'],
                    'disc_price' => $validator->validated()['discount_price'],
                    'weight_g' => $validator->validated()['weight_in_gram'],
                    'min_purchase' => $validator->validated()['min_purchase'],
                    'store_id' => $validator->validated()['store_id'],
                    'category_id' => $validator->validated()['category_id']
                ];

                if ($validator->validated()['discount_percent'] === null || $validator->validated()['discount_percent'] === '') {
                    if ($validator->validated()['discount_price'] !== null && $validator->validated()['discount_price'] !== '') {
                        $inputData['disc_percent'] = (floatval($validator->validated()['discount_price']) / floatval($validator->validated()['initial_price'])) * 100;
                    } else {
                        $inputData['disc_percent'] = 0;
                    }
                } else {
                    $inputData['disc_price'] = round((floatval($validator->validated()['discount_percent']) / 100) * floatval($validator->validated()['initial_price']), 2);
                }

                $inputData['net_price'] = $inputData['initial_price'] - $inputData['disc_price'];

                $inputProduct = Product::insert($inputData);

                if ($inputProduct) {
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
    }

    public function show($id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $product = $this->product->findProduct($id);

                if (count($product) > 0) {
                    return response()->json([
                        'success' => true,
                        'data' => ProductResource::make($product[0])
                        // 'as' => $product
                    ], 200);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Data with ID = '.$id.' not found!'
                    ], 404);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'path parameter ID must be numeric'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide path parameter ID!'
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $validator = Validator::make($request->all(), $this->rules, $this->messages);


                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => true,
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    $validateCategory = ProductCategory::find($validator->validated()['category_id']);

                    if ($validateCategory === null) {
                        $validateCategory === null ? $errors['category_id'] = 'Category ID not found' : '';

                        return response()->json([
                            'success' => false,
                            'error' => true,
                            'message' => 'The given data was invalid',
                            'errors' => $errors
                        ], 400);
                    } else {
                        $updateData = [
                            'name' => $validator->validated()['name'],
                            'condition' => $validator->validated()['condition'],
                            'initial_price' => $validator->validated()['initial_price'],
                            'disc_percent' => $validator->validated()['discount_percent'],
                            'disc_price' => $validator->validated()['discount_price'],
                            'weight_g' => $validator->validated()['weight_in_gram'],
                            'min_purchase' => $validator->validated()['min_purchase'],
                            'category_id' => $validator->validated()['category_id']
                        ];

                        if ($validator->validated()['discount_percent'] === null || $validator->validated()['discount_percent'] === '') {
                            if ($validator->validated()['discount_price'] !== null && $validator->validated()['discount_price'] !== '') {
                                $updateData['disc_percent'] = (floatval($validator->validated()['discount_price']) / floatval($validator->validated()['initial_price'])) * 100;
                            } else {
                                $updateData['disc_percent'] = 0;
                            }
                        } else {
                            $updateData['disc_price'] = round((floatval($validator->validated()['discount_percent']) / 100) * floatval($validator->validated()['initial_price']), 2);
                        }

                        $updateData['net_price'] = $updateData['initial_price'] - $updateData['disc_price'];

                        $updateProduct = Product::where('id', $id)->update($updateData);

                        if ($updateProduct) {
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
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Pramater ID is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }

    public function destroy($id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $deleteProduct = Product::where('id', $id)->delete();

                if ($deleteProduct) {
                    return response()->json([
                        'success' => true,
                        'error' => false,
                        'message' => 'Data deleted'
                    ], 200);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Failed to delete data'
                    ], 500);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Pramater ID is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide an ID!'
            ], 400);
        }
    }
}
