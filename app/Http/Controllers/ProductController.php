<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\KeywordLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\DB;
use TimeHelp;

class ProductController extends Controller
{
    protected $productModel;
    protected $keywordLogModel;
    protected $rules;
    protected $messages;

    public function __construct(){
        $this->productModel = new Product();
        $this->keywordLogModel = new KeywordLog();
        $this->rules = [
            'name' => 'required|min:1|max:60',
            'condition' => ['required', Rule::in(["N","SH"])],
            'initial_price' => 'required|numeric|between:0,9999999999999.99',
            'discount_percent' => 'present|nullable|numeric|between:0,100.00',
            'discount_price' => 'present|nullable|numeric',
            'weight_in_gram' => 'required|numeric',
            'min_purchase' => 'required|numeric',
            'category_id' => 'required',
            'description' => 'string|nullable|max:200',
            'status_stock' => ['required', Rule::in(["PO","R"])],
            'available_date' => 'required_if:stock_status,PO|required_without:available_days|date_format:Y-m-d',
            'available_days' => 'required_if:stock_status,PO|required_without:available_date|numeric',
            'available_timezone' => 'required_unless:available_date,null'
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
            'description.string' => 'Description must be a string',
            'description.string' => 'Description cannot be more than 200 character',
            'status_stock.required' => 'Stock status is required',
            'status_stock.in' => 'Value must be "PO" for Pre-Order or "R" for In-stock',
            'available_date.required_if' => "One of stock available date or stock available days is required",
            'available_date.required_without' => "One of stock available date or stock available days is required",
            'available_date.date_format' => "Stock available date must following this format: Y-m-d",
            'available_days.required_if' => "One of stock available date or stock available days is required",
            'available_days.required_without' => "One of stock available date or stock available days is required",
            'available_days.numeric' => "Stock available days must be numeric",
            'available_timezone.required_unless' => 'Timezone is required when available_date not null'
        ];
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search ?? null,
            'user_id' => auth()->guard('api')->user()->id ?? null,
            'condition' => $request->condition ?? null,
            'min_price' => $request->min_price && is_numeric($request->min_price) ? round(floatval($request->min_price), 2) : 0,
            'max_price' => $request->max_price && is_numeric($request->max_price) ? round(floatval($request->max_price), 2) : null,
            'city' => $request->city ?? null,
            'province' => $request->province ?? null,
            'page' => $request->sort !== 'relevant' ? $request->page : 'all',
            'limit' => $request->sort !== 'relevant' && $request->per_page && $request->per_page > 0 ? $request->per_page : 200,
            'except' => $request->sort !== 'relevant' && $request->except ? $request->except : null
        ];

        // Set Offset
        if ($request->sort !== 'relevant') {
            if ($request->page > 1) {
                $filters['offset'] = (intval($request->page) - 1) * $filters['limit'];
            } else {
                $filters['offset'] = 0;
            }
        }

        // Set Sorting
        switch ($request->sort) {
            case "relevant":
                $filters['sort'] = false;
                break;
            case "id":
                $filters['sort'] = 'id';
                $filters['order'] = $request->order ?? 'ASC';
                break;
            case "uuid":
                $filters['sort'] = 'product_uuid';
                $filters['order'] = $request->order ?? 'ASC';
                break;
            // case "popularity":
            //     $filters['order'] = $request->order ?? 'ASC';
            //     $filters['sort'] = 'popularity_poin';
            //     break;
            case "price":
                $filters['sort'] = 'net_price';
                $filters['order'] = $request->order ?? 'ASC';
                break;
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

        // Write Log Client keyword
        if ($request->search && is_string($request->search)) {
            $userID = auth()->guard('api')->user() ? auth()->guard('api')->user()->id : null;

            $clientIP = $_SERVER['REMOTE_ADDR'] ?? $request->ip();
            // Check for the X-Forwarded-For header
            if ($request->header('X-Forwarded-For')) {
                $forwardedIPs = explode(',', $request->header('X-Forwarded-For'));
                $clientIP = trim(end($forwardedIPs));
            }

            $check = $this->keywordLogModel->getKeywordLog($clientIP, $userID, $filters['search'])->first();
            if (!$check) {
                $this->keywordLogModel->writeLogKeyword([
                    'client_ip' => $clientIP,
                    'user_id' => $userID,
                    'keyword' => $request->search,
                    'created_at' => now(),
                    'created_tz' => date_default_timezone_get()
                ]);
            }
        }

        // Get Product Data
        $products = $this->productModel->getProducts($filters);

        return response()->json([
            'success' => true,
            'message' => ($products && count($products) > 0 ? 'Data found' : 'No data available'),
            'search' => $request->search ?? null,
            'sort_by' => $request->sort ?? null,
            'sort_order' => $request->order ?? null,
            'page' => $request->page ?? null,
            'row_per_page' => $filters['limit'],
            'count_data' => $products ? count($products) : null,
            'count_all' => $this->productModel->countAll()[0]->count_all,
            'data' => $products ? ProductResource::collection($products) : null
        ], 200);
    }

    public function store(Request $request)
    {
        $this->rules['store_id'] = 'required';
        $this->rules['image'] = 'image|file|max:2048';
        $this->messages['store_id'] = 'Store ID is required';
        $this->messages['images.image'] = 'File must be an image (jpg, jpeg, png, bmp, gif, svg, or webp)';
        $this->messages['images.max'] = 'File size can not be greater than 2MB (2048 KB)';

        $validator = Validator::make($request->all(), $this->rules, $this->messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $store = Store::where('user_id', auth()->guard('api')->user()->id)->find($request->store_id);
            if ($store) {
                $inputData = [
                    'product_uuid' => Str::uuid(),
                    'name' => $validator->validated()['name'],
                    'condition' => $validator->validated()['condition'],
                    'initial_price' => $validator->validated()['initial_price'],
                    'disc_percent' => $validator->validated()['discount_percent'],
                    'disc_price' => $validator->validated()['discount_price'],
                    'weight_g' => $validator->validated()['weight_in_gram'],
                    'min_purchase' => $validator->validated()['min_purchase'],
                    'store_id' => $validator->validated()['store_id'],
                    'category_id' => $validator->validated()['category_id'],
                    'description' => htmlspecialchars($validator->validated()['description']),
                    'stock_status' => $validator->validated()['status_stock'],
                    'stock_available_days' => $validator->validated()['status_stock'] === 'PO' && isset($validator->validated()['available_days']) ? $validator->validated()['available_days'] : null,
                    'stock_available_date' => $validator->validated()['status_stock'] === 'PO' && isset($validator->validated()['available_date']) && !isset($validator->validated()['available_days']) ? TimeHelp::convertTz($validator->validated()['available_date'], $validator->validated()['available_timezone'], 'UTC') : null,
                    'created_at' => now(),
                    'created_tz' => date_default_timezone_get(),
                    'updated_at' => now(),
                    'updated_tz' => date_default_timezone_get()
                ];

                // Calculate disc & net price after discount
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
                    if ($request->file('images')) {
                        // Set input value product images
                        foreach ($request->file('images') as $key => $img) {
                            $inputImg[] = [
                                'product_uuid' => $inputData['product_uuid'],
                                'path' => $img->store('product-images/'.$store->domain),
                                'mime' => $img->getMimeType(),
                                'created_at' => now(),
                                'created_tz' => date_default_timezone_get(),
                                'updated_at' => now(),
                                'updated_tz' => date_default_timezone_get()
                            ];
                        }

                        $inputImg = ProductImage::insert($inputImg);
                    }

                    return response()->json([
                        'success' => true,
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
                    'message' => 'The given data was invalid',
                    'errors' => [
                        'store_id' => [
                            'Store not found'
                        ]
                    ]
                ], 400);
            }
        }
    }

    public function show($id)
    {
        if ($id) {
            $product = $this->productModel->findProduct($id)->first();

            if ($product) {
                return response()->json([
                    'success' => true,
                    'data' => ProductResource::make($product)
                    // 'data' => $product
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Data with ID / UUID = '.$id.' not found!'
                ], 404);
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
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    $validateCategory = ProductCategory::find($validator->validated()['category_id']);

                    if ($validateCategory === null) {
                        $validateCategory === null ? $errors['category_id'] = 'Category ID not found' : '';

                        return response()->json([
                            'success' => false,
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

    public function addImage (Request $request, $id) {
        if ($id) {
            // Check Logged User Store
            $store = Store::select('id', 'domain')->where('user_id', auth()->guard('api')->user()->id)->get();

            if ($store) {
                // $product = Product::where('store_id', $store->id)->find($id);
                $product = $this->productModel->checkProductStore($id, $store->first()->id);

                if ($product || count($product) > 0) {
                    $validator = Validator::make($request->file('image'), ['image.*' => 'required|image|file|max:2048'], [
                        'image.*.required' => 'Image field is required',
                        'image.*.image' => 'File must be an image (jpg, jpeg, png, bmp, gif, svg, or webp)',
                        'image.*.max' => 'File size can not be greater than 2MB (2048 KB)'
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'The given data was invalid',
                            'errors' => $validator->errors()
                        ], 400);
                    } else {
                        // Set input value product images
                        $inputImg = null;

                        foreach ($request->file('image') as $key => $img) {
                            // dd($img);
                            $inputImg[] = [
                                'product_uuid' => $product[0]->product_uuid,
                                'path' => $img->store('product-images/'.$store[0]->domain),
                                'mime' => $img->getMimeType(),
                                'created_at' => now(),
                                'created_tz' => date_default_timezone_get(),
                                'updated_at' => now(),
                                'updated_tz' => date_default_timezone_get()
                            ];
                        }
                        $inputImg = ProductImage::insert($inputImg);

                        if ($inputImg) {
                            return response()->json([
                                'success' => true,
                                'message' => 'New image(s) added successfully'
                            ], 200);
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed to upload data'
                            ], 500);
                        }
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'You are unauthorized to access / alter this data'
                ], 401);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }

    public function deleteImage (Request $request, $id) {
        if ($id) {
            // Check Logged User Store
            $store = Store::select('id', 'domain')->where('user_id', auth()->guard('api')->user()->id)->get();

            if ($store) {
                // $product = Product::where('store_id', $store->id)->find($id);
                $product = $this->productModel->checkProductStore($id, $store[0]->id);

                if ($product) {
                    if ($request->image_id) {
                        $deleteImg = ProductImage::destroy($request->image_id);

                        if ($deleteImg) {
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
                            'message' => 'Please provide file ID'
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ], 404);
                }

                return response()->json([
                    'success' => false,
                    'id' => $id,
                    'sotor' => $store[0]->id,
                    'q' => $this->productModel->test($id, $store[0]->id),
                    'message' => $product
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'You are unauthorized to access / alter this data'
                ], 401);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }

    }

    public function similar (Request $request, $id) {
        if ($id) {
            $product = $this->productModel->findProduct($id)->first();

            if ($product) {
                $filters = [
                    'page' => $request->sort !== 'relevant' ? $request->page : 'all',
                    'limit' => $request->sort !== 'relevant' && $request->per_page && $request->per_page > 0 ? $request->per_page : 200
                ];

                // Set Offset
                if ($request->sort !== 'relevant') {
                    if ($request->page > 1) {
                        $filters['offset'] = (intval($request->page) - 1) * $filters['limit'];
                    } else {
                        $filters['offset'] = 0;
                    }
                }

                // Set Sorting
                switch ($request->sort) {
                    case "relevant":
                        $filters['sort'] = false;
                        break;
                    case "id":
                        $filters['sort'] = 'id';
                        $filters['order'] = $request->order ?? 'ASC';
                        break;
                    case "uuid":
                        $filters['sort'] = 'product_uuid';
                        $filters['order'] = $request->order ?? 'ASC';
                        break;
                    // case "popularity":
                    //     $filters['order'] = $request->order ?? 'ASC';
                    //     $filters['sort'] = 'popularity_poin';
                    //     break;
                    case "price":
                        $filters['sort'] = 'net_price';
                        $filters['order'] = $request->order ?? 'ASC';
                        break;
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
                $arrName = preg_split('/\s+/', preg_replace('/[\-!@^-_=|;\\\\&\/#,\s\s+()$~%.\'":*?<>{}\[\]]/', ' ', $product->product_name), -1);
                $similar = $this->productModel->similarProduct($arrName, [0 => $product->category_id], $filters);

                return response()->json([
                    'success' => true,
                    'message' => ($similar && count($similar) > 0 ? 'Data found' : 'No data available'),
                    'sort_by' => $request->sort ?? null,
                    'sort_order' => $request->order ?? null,
                    'page' => 'all',
                    'count_data' => $similar ? count($similar) : null,
                    'count_all' => $this->productModel->countAllSimilar($arrName, [0 => $product->category_id])[0]->count_all,
                    'data' => $similar ? ProductResource::collection($similar) : null
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Data with ID / UUID = '.$id.' not found!'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Please provide path parameter ID!'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $id
        ], 200);
    }

    /** Get multiple product */
    public function showProducts (Request $request) {
        if ($request->ids && is_array($request->ids)) {
            // dd($request->ids);
            $products = $this->productModel->findProducts($request->ids, 'id');

            return response()->json([
                'success' => true,
                'message' => 'Data Found',
                'count_data' => count($products),
                'data' => $products ? ProductResource::collection($products) : null
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide parameter "ids" (must be an array)!'
            ], 400);
        }
    }
}
