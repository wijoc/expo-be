<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\StoreCategory;
use App\Http\Resources\StoreResource;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->stores = new Store();
        $this->rules = [
            'store_name' => 'required|min:1|max:60|unique:App\Models\Store',
            'domain' => 'required|min:1|max:25|regex:/^((?!-)[\d\w\-]{1,25}(?<!-))+$/|unique:App\Models\Store',
            'email' => 'nullable|email:dns',
            'phone' => ['nullable', 'regex:/^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$/', 'min:8'],
            'whatsapp' => ['nullable', 'regex:/^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$/', 'min:8'],
            'image' => 'image|file|max:2048',
            'full_address' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'province_id' => 'required',
            'category_id' => 'required'
        ];
        $this->messages = [
            'store_name.required' => 'Store name is required',
            'store_name.min' => 'Store name must be at least 1 character',
            'store_name.max' => 'Store name can not be more than 60 character',
            'store_name.unique' => 'Store name is already used',
            'domain.required' => 'Store domain is required',
            'domain.min' => 'Store domain must be at least 1 character',
            'domain.max' => 'Store domain can not be more than 25 character',
            'domain.regex' => 'Store domain invalid. Allowed character : Uppercase and lowercase letter, 0-9, "-", "_" and should not start or and with "-"',
            'domain.unique' => 'Store domain is already used',
            'email.email' => 'Email is invalid',
            'phone.regex' => 'Phone Number is invalid. Allowed characted : + and 0-9; without space, "-", or "\"; Starting with "00" is not allowed use +(prefix) instead',
            'phone.min' => 'Mobilephone Number must ber at least 8 character (including country prefix)',
            'whatsapp.regex' => 'Whatsapp Number is invalid. Allowed characted : + and 0-9; without space, "-", or "\"; Starting with "00" is not allowed use +(prefix) instead',
            'whatsapp.min' => 'Whatsapp Number must be at least 8 character (including country prefix)',
            'image.image' => 'File must be an image (jpg, jpeg, png, bmp, gif, svg, or webp)',
            'image.max' => 'File size can not be greater than 2MB (2048 KB)',
            'full_address.required' => 'Adress is required',
            'district_id.required' => 'District ID is required',
            'city_id.required' => 'City ID is required',
            'province_id.required' => 'Province ID is required',
            'category_id.required' => 'Category ID is required',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storesData = $this->stores->getStores($request);

        if ($storesData) {
            $data = [];
            foreach ($storesData as $key => $value) {
                if (!array_key_exists($value['store_id'], $data)) {
                    $data[$value['store_id']] = $value;
                    $data[$value['store_id']]['products'] = [];
                }

                if ($value['product_id'] && $value['product_id'] !== '') {
                    $prd = array(
                        'id' => $value['product_id'],
                        'name' => $value['product_name'],
                        'store_id' => $value['store_id']
                    );
                    $prods = $data[$value['store_id']]['products'];
                    array_push($prods, $prd);

                    $data[$value['store_id']]['products'] = $prods ?? [];
                }
            }

            return response()->json([
                'success' => true,
                'error' => false,
                'count_data' => count(array_values($data)),
                // 'count_all' +> ,
                'data' => StoreResource::collection(array_values($data))
            ], 200);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No data available!'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->rules['user_id'] = 'required|unique:App\Models\Store';
        $this->messages['user_id.required'] = 'User ID is required';
        $this->messages['user_id.unique'] = 'User already has store registered';

        $validator = Validator::make($request->all(), $this->rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $validateUser = User::find($validator->validated()['user_id']);
            $validateProvince = Province::find($validator->validated()['province_id']);
            $validateCity = City::find($validator->validated()['city_id']);
            $validateDistrict = District::find($validator->validated()['district_id']);
            $validateCategory = StoreCategory::find($validator->validated()['category_id']);

            if ($validateUser === null || $validateProvince === null || $validateCity === null || $validateDistrict === null || $validateCategory === null) {
                $validateUser === null ? $errors['user_id'][] = 'User ID not found' : '';
                $validateProvince === null ? $errors['province_id'][] = 'Province ID not found' : '';
                $validateCity === null ? $errors['city_id'][] = 'City ID not found' : '';
                $validateDistrict === null ? $errors['district_id'][] = 'District ID not found' : '';
                $validateCategory === null ? $errors['category_id'][] = 'Category ID not found' : '';

                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'The given data was invalid',
                    'errors' => $errors
                ], 400);
            } else {
                $validated = [
                    'store_name' => $validator->validated()['store_name'],
                    'domain' => $validator->validated()['domain'],
                    'email' => $validator->validated()['email'],
                    'phone' => $validator->validated()['phone'],
                    'whatsapp' => $validator->validated()['whatsapp'],
                    'full_address' => $validator->validated()['full_address'],
                    'district_id' => $validator->validated()['district_id'],
                    'city_id' => $validator->validated()['city_id'],
                    'province_id' => $validator->validated()['province_id'],
                    'category_id' => $validator->validated()['category_id'],
                    'user_id' => $validator->validated()['user_id'],
                    'image_path' => $request->file('image')->store('store-images'),
                    'image_mime' => $request->file('image')->getMimeType()
                ];

                $inputStore = Store::insert($validated);

                if ($inputStore) {
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        if ($slug) {
            $store = $this->store->findStore($slug);

            if (count($store) > 0) {
                return response()->json([
                    'success' => true,
                    'data' => StoreResource::make($store[0])
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Data with ID = '.$slug.' or domain = '.$slug.' not found!'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide path parameter (ID or slug domain)!'
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $this->rules['store_name'] = 'required|min:1|max:60|unique:App\Models\Store,store_name, '.$id;
                $this->rules['domain'] = 'required|min:1|max:25|regex:/^((?!-)[\d\w\-]{1,25}(?<!-))+$/|unique:App\Models\Store,domain, '.$id;

                $validator = Validator::make($request->all(), $this->rules, $this->messages);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => true,
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    $validateProvince = Province::find($validator->validated()['province_id']);
                    $validateCity = City::find($validator->validated()['city_id']);
                    $validateDistrict = District::find($validator->validated()['district_id']);
                    $validateCategory = StoreCategory::find($validator->validated()['category_id']);

                    if ($validateProvince === null || $validateCity === null || $validateDistrict === null || $validateCategory === null) {
                        $validateProvince === null ? $errors['province_id'] = 'Province ID not found' : '';
                        $validateCity === null ? $errors['city_id'] = 'City ID not found' : '';
                        $validateDistrict === null ? $errors['district_id'] = 'District ID not found' : '';
                        $validateCategory === null ? $errors['category_id'] = 'Category ID not found' : '';

                        return response()->json([
                            'success' => false,
                            'error' => true,
                            'message' => 'The given data was invalid',
                            'errors' => $errors
                        ], 400);
                    } else {
                        $inputData = array(
                            "store_name" => $validator->validated()['store_name'],
                            "domain" => $validator->validated()['domain'],
                            "email" => $validator->validated()['email'],
                            "phone" => $validator->validated()['phone'],
                            "whatsapp" => $validator->validated()['whatsapp'],
                            "full_address" => $validator->validated()['full_address'],
                            "district_id" => $validator->validated()['district_id'],
                            "city_id" => $validator->validated()['city_id'],
                            "province_id" => $validator->validated()['province_id'],
                            "category_id" => $validator->validated()['category_id']
                        );
                        $updateStore = Store::where('id', $id)->update($inputData);

                        if ($updateStore) {
                            return response()->json([
                                'success' => true,
                                'error' => true,
                                'message' => 'Data updated successfully'
                            ], 200);
                        } else {
                            return response()->json([
                                'success' => true,
                                'error' => true,
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id) {
            if (is_numeric($id)) {
                $deleteStore = Store::where('id', $id)->delete();

                if ($deleteStore) {
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
