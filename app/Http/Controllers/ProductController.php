<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\KeywordLog;
use App\Models\ShowProductLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $productModel;
    protected $productLogModel;
    protected $keywordLogModel;
    protected $rules;
    protected $messages;

    public function __construct(){
        $this->productModel = new Product();
        $this->productLogModel = new ShowProductLog();
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

        // Set Paginate
        if ($request->page !== 'all') {
            $filters['page'] = $request->page;
            $filters['limit'] = ($request->per_page && $request->per_page > 0 ? $request->per_page : 100);
            if ($request->page > 1) $filters['offset'] = $request->page - 1 * $filters['limit'];
        }

        // Set limit IF ONLY SORT BY "relevant"
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
                $pageData = $this->productLogModel->thisPageLog($filters);

                if ($pageData && count($pageData) > 0) {
                    $filters['where_in'] = explode(',', $pageData[0]->store_id);
                }
                if ($request->page > 1 && !$pageData || count($pageData) <= 0) {
                    $prevData = $this->productLogModel->previousLog($filters);
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

        // Get Product Data
        $products = $this->productModel->getProducts($request);

        return response()->json([
            'success' => true,
            'message' => ($products && count($products) > 0 ? 'Data found' : 'No data available'),
            'search' => $request->search ?? null,
            'sort_by' => $request->sort ?? null,
            'sort_order' => $request->order ?? null,
            'page' => $request->page ?? null,
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
                    'name' => $request->name,
                    'condition' => $request->condition,
                    'initial_price' => $request->initial_price,
                    'disc_percent' => $request->discount_percent,
                    'disc_price' => $request->discount_price,
                    'weight_g' => $request->weight_in_gram,
                    'min_purchase' => $request->min_purchase,
                    'store_id' => $request->store_id,
                    'category_id' => $request->category_id,
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
                $arrName = preg_split('/\s+/', preg_replace('/[\-!@^-_=|;\\\\&\/#,\s\s+()$~%.\'":*?<>{}\[\]]/', ' ', $product->product_name), -1);
                $similar = $this->productModel->similarProduct($arrName, [0 => $product->category_id]);

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
}
