<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\KeywordLog;
use App\Models\ShowStoreLog;
use App\Models\StoreCategory;
use App\Http\Resources\StoreResource;
use GuzzleHttp\Psr7\Message;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->storeModel = new Store();
        $this->productModel = new Product();
        $this->storeLogModel = new ShowStoreLog();
        $this->keywordLogModel = new KeywordLog();

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
        $filters['client_ip'] = $_SERVER['REMOTE_ADDR'] ?? $request->ip();
        $filters['user_id'] = auth()->guard('api')->user()->id ?? null;
        $filters['search'] = $request->search ?? null;

        // Set Sorting
        switch ($request->sort) {
            case "relevant":
                $filters['sort'] = false;
                break;
            // case "featured":
            // case "popularity":
            //     $filters['order'] = $request->order ?? 'ASC';
            //     $filters['sort'] = 'popularity_poin';
            //     break;
            // case "price":
            //     $filters['sort'] = 'net_price';
            //     break;
            case "name":
                $filters['order'] = $request->order ?? 'ASC';
                $filters['sort'] = 'store_name';
                break;
            case "newest":
            case "latest":
                $filters['order'] = 'ASC';
                $filters['sort'] = 'created_at';
                break;
            case "oldest":
                $filters['order'] = 'DESC';
                $filters['sort'] = 'created_at';
                break;
            default:
                $filters['order'] = $request->order ?? 'ASC';
                $filters['sort'] = false;
        }

        // Set Limit & Offset
        if ($request->page !== 'all') {
            $filters['page'] = $request->page;
            $filters['limit'] = ($request->per_page && $request->per_page > 0 ? $request->per_page : 30);
            if ($request->page > 1) $filters['offset'] = $request->page - 1 * $filters['limit'];
        }

        // Set Limit IF ONLY SORT BY "relevant"
        if (!$request->sort || $request->sort === "relevant") {
            if (!$request->search) {
                $last3Keyword = $this->keywordLogModel->getLastKeyword($filters, 3);

                if ($last3Keyword && count($last3Keyword) > 1) {
                    $keywords = [];
                    foreach ($last3Keyword as $key => $value) {
                        array_push($keywords, $value['keyword']);
                    }
                    $filters['multi-search'] = $keywords;
                } else if (count($last3Keyword) == 1) {
                    $filters['search'] = $last3Keyword[0]['keyword'];
                }
            }

            if ($request->page !== 'all') {
                $filters['timelimit'] = date('Y-m-d H:i:s', strtotime('-1 hour'));
                $pageData = $this->storeLogModel->thisPageLog($filters);

                if ($pageData && count($pageData) > 0) {
                    $filters['where_in'] = explode(',', $pageData[0]->store_id);
                }
                if ($request->page > 1 && !$pageData || count($pageData) <= 0) {
                    $prevData = $this->storeLogModel->previousLog($filters);
                    $notIn = [];

                    if ($prevData && count($prevData) > 0){
                        foreach($prevData as $key => $value) {
                            $notIn = array_merge($notIn, explode(',', $value['store_id']));
                        }

                        $filters['where_not_in'] = $notIn;
                    }
                }
            }
        }

        // Log Client keyword
        if ($request->search) {
            KeywordLog::upsert([
                'client_ip' => $filters['client_ip'],
                'user_id' => auth()->guard('api')->user()->id ?? NULL,
                'keyword' => $request->search,
                'created_at' => now(),
                'created_tz' => date_default_timezone_get(),
                'updated_at' => now(),
                'updated_tz' => date_default_timezone_get()
            ], ['client_ip', 'user_id', 'keyword'], ['updated_at', 'updated_tz']);
        }

        // Get Store Data
        if ($request->with_product) {
            $storesData = $this->storeModel->getStores($filters);
        } else {
            $storesData = $this->storeModel->getAllStore($filters);
        }

        if ($storesData && count($storesData) > 0) {
            if ($request->with_product) {
                $data = [];
                foreach ($storesData as $key => $value) {
                    if (!array_key_exists($value['store_id'], $data)) {
                        $data[$value['store_id']] = $value;
                        $data[$value['store_id']]['products'] = [];
                    }

                    if ($value['product_id'] && $value['product_id'] !== '') {
                        $prd = array(
                            'id' => $value['product_id'],
                            'uuid' => $value['product_uuid'],
                            'name' => $value['product_name'],
                            'price' => $value['net_price'],
                            'store_id' => $value['store_id']
                        );
                        $prods = $data[$value['store_id']]['products'];
                        array_push($prods, $prd);

                        $data[$value['store_id']]['products'] = $prods ?? [];
                    }
                }

                $storesData = array_values($data);
            }

            // Log showed row (ONLY IF SORT "relevant")
            if ($request->page && $request->page !== 'all' && !$request->sort || $request->sort === "relevant") {
                $storeID = [];
                foreach($storesData as $values) {
                    array_push($storeID, $values['store_id']);
                }
                $storeID = implode(',', $storeID);

                /** Update client search log IF YOU USING POSTGRESQL READ THIS !
                 * Since non of field ['client_ip', 'user_id', 'page', 'keyword'] is unique
                 * and sometime value is NULL, upsert not working for postgreSQL (maybe i don't know how)
                 * so here this will check if row-data exist, and determine whether to insert or update.
                 *
                 * IF YOU USING MySQL / MairaDB, i think upsert will do just fine.
                 */

                $check = $this->storeLogModel->thisPageLog([
                    'client_ip' => $filters['client_ip'] ?? null,
                    'user_id' => auth()->guard('api')->user() ? auth()->guard('api')->user()->id : NULL,
                    'page' => $request->page,
                    'search' => $request->search ?? null,
                    'timelimit' => false
                ])->first();

                // ShowStoreLog::upsert([
                //     'client_ip' => $filters['client_ip'] ?? null,
                //     'user_id' => auth()->guard('api')->user() ? auth()->guard('api')->user()->id : NULL,
                //     'page' => $request->page,
                //     'keyword' => $request->search ?? null,
                //     'store_id' => $storeID,
                //     'created_at' => now(),
                //     'created_tz' => date_default_timezone_get(),
                //     'updated_at' => now(),
                //     'updated_tz' => date_default_timezone_get()
                // ], ['client_ip', 'user_id', 'page', 'keyword'], ['store_id', 'updated_at', 'updated_tz']);

                if ($check) {
                    ShowStoreLog::where('id', $check['id'])->update([
                        'store_id' => $storeID,
                        'updated_at' => now(),
                        'updated_tz' => date_default_timezone_get()
                    ]);
                } else {
                    ShowStoreLog::insert([
                        'client_ip' => $filters['client_ip'] ?? null,
                        'user_id' => auth()->guard('api')->user() ? auth()->guard('api')->user()->id : NULL,
                        'page' => $request->page,
                        'keyword' => null,
                        'store_id' => $storeID,
                        'created_at' => now(),
                        'created_tz' => date_default_timezone_get(),
                        'updated_at' => now(),
                        'updated_tz' => date_default_timezone_get()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Found',
                'search' => $request->search,
                'sort_by' => $request->sort,
                'sort_order' => $filters['order'],
                'page' => $request->page,
                'row_per_page' => $filters['limit'],
                'count_data' => count($storesData),
                'count_all' => $this->storeModel->countAll($filters)->first()['count_all'],
                'data' => StoreResource::collection($storesData)
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No data available'
            ], 200);
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

        $request->merge(['user_id' => auth()->guard('api')->user()->id]);

        $validator = Validator::make($request->all(), $this->rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
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
                $validateUser === null ? $errors['user_id'][] = 'User not found' : '';
                $validateProvince === null ? $errors['province_id'][] = 'Province ID not found' : '';
                $validateCity === null ? $errors['city_id'][] = 'City ID not found' : '';
                $validateDistrict === null ? $errors['district_id'][] = 'District ID not found' : '';
                $validateCategory === null ? $errors['category_id'][] = 'Category ID not found' : '';

                return response()->json([
                    'success' => false,
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
                    'image_path' => $request->file('image') ? $request->file('image')->store('store-images') : null,
                    'image_mime' => $request->file('image') ? $request->file('image')->getMimeType() : null,
                    'created_at' => now(),
                    'created_tz' => date_default_timezone_get(),
                    'updated_at' => now(),
                    'updated_tz' => date_default_timezone_get()
                ];

                $inputStore = Store::insert($validated);

                if ($inputStore) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Success add new data'
                    ], 201);
                } else {
                    return response()->json([
                        'success' => false,
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
    public function show(Request $request, $slug)
    {
        if ($slug) {
            $store = $this->storeModel->findStore($slug)[0];
            if ($request->with_product) {
                $store['products'] = ProductResource::collection($this->productModel->getProducts(['store'], $store->store_id));
            }

            if ($store) {
                return response()->json([
                    'success' => true,
                    'data' => StoreResource::make($store)
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data with ID = '.$slug.' or domain = '.$slug.' not found!'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
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
                $store = Store::where('user_id', auth()->guard('api')->user()->id)->find($id);
                if ($store) {
                    $this->rules['store_name'] = 'required|min:1|max:60|unique:App\Models\Store,store_name, '.$id;
                    $this->rules['domain'] = 'required|min:1|max:25|regex:/^((?!-)[\d\w\-]{1,25}(?<!-))+$/|unique:App\Models\Store,domain, '.$id;

                    $validator = Validator::make($request->all(), $this->rules, $this->messages);

                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
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
                                'message' => 'The given data was invalid',
                                'errors' => $errors
                            ], 400);
                        } else {
                            $updateData = array(
                                "store_name" => $validator->validated()['store_name'],
                                "domain" => $validator->validated()['domain'],
                                "email" => $validator->validated()['email'],
                                "phone" => $validator->validated()['phone'],
                                "whatsapp" => $validator->validated()['whatsapp'],
                                "full_address" => $validator->validated()['full_address'],
                                "district_id" => $validator->validated()['district_id'],
                                "city_id" => $validator->validated()['city_id'],
                                "province_id" => $validator->validated()['province_id'],
                                "category_id" => $validator->validated()['category_id'],
                                "updated_at" => now(),
                                "updated_tz" => date_default_timezone_get()
                            );
                            $updateStore = Store::where('id', $id)->update($updateData);

                            if ($updateStore) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Data updated successfully'
                                ], 200);
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Failed to update data'
                                ], 500);
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Pramater ID is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
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
                $store = Store::where('user_id', auth()->guard('api')->user()->id)->find($id);
                if ($store) {
                    $deleteStore = Store::where('id', $id)->delete();

                    if ($deleteStore) {
                        if ($store->image_path) {
                            Storage::delete($store->image_path);
                        }

                        return response()->json([
                            'success' => true,
                            'message' => 'Data deleted'
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to delete data'
                        ], 500);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Pramater ID is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide an ID!'
            ], 400);
        }
    }

    public function productInStore(Request $request, $id) {
        $request->merge(['store' => $id]);
        $products = $this->productModel->getProducts($request);

        if ($products && count($products) > 0) {
            return response()->json([
                'success' => true,
                'count_data' => count($products),
                'count_all' => $this->productModel->countAll($request)[0]->count_all,
                'data' => ProductResource::collection($products)
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 200);
        }
    }

    public function updateImage (Request $request, $id) {
        if ($id) {
            if (is_numeric($id)) {
                $store = Store::where('user_id', auth()->guard('api')->user()->id)->find($id);
                if ($store) {
                    $validator = Validator::make($request->only('image'), ['image' => 'required|image|file|max:2048'], [
                        'image.required' => 'Image field is required',
                        'image.image' => 'File must be an image (jpg, jpeg, png, bmp, gif, svg, or webp)',
                        'image.max' => 'File size can not be greater than 2MB (2048 KB)'
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'The given data was invalid',
                            'errors' => $validator->errors()
                        ], 400);
                    } else {
                        $updateData = [
                            'image_path' => $request->file('image')->store('store-images'),
                            'image_mime' => $request->file('image')->getMimeType()
                        ];

                        $updateImage = Store::where('id', $id)->update($updateData);

                        if ($updateImage) {
                            if ($store->image_path) {
                                Storage::delete($store->image_path);
                            }

                            return response()->json([
                                'success' => true,
                                'message' => 'Data updated successfully'
                            ], 200);
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed to update data'
                            ], 500);
                        }
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Pramater ID is invalid'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }
}
