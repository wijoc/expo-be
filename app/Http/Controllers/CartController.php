<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Product;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    protected $cartModel;
    protected $productModel;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }

    public function index(Request $request)
    {
        $cart = $this->cartModel->getCart(auth()->guard('api')->user()->id);
        $totalCart = 0;
        if ($cart) {
            $data = [];
            foreach ($cart as $value) {
                $data[$value->store_id]['store_name'] = $value->store_name;
                $data[$value->store_id]['store_domain'] = $value->domain;

                if (!array_key_exists('store_subtotal_cart', $data[$value->store_id])) {
                    $data[$value->store_id]['store_subtotal_cart'] = 0 + (floatval($value->product_qty) * floatval($value->net_price));
                } else {
                    $data[$value->store_id]['store_subtotal_cart'] = $data[$value->store_id]['store_subtotal_cart'] + (floatval($value->product_qty) * floatval($value->net_price));
                }

                if (!array_key_exists('items', $data[$value->store_id])) {
                    $data[$value->store_id]['items'] = [];
                    $items = $data[$value->store_id]['items'];
                } else {
                    $items = $data[$value->store_id]['items'];
                }

                array_push($items, [
                    'cart_id' => $value->id,
                    'product_name' => $value->name,
                    'product_id' => $value->product_id,
                    'product_uuid' => $value->product_uuid,
                    'product_initial_price' => floatval($value->initial_price),
                    'product_net_price' => floatval($value->net_price),
                    'product_discount_percent' => $value->disc_percent,
                    'product_discount_price' => $value->disc_price,
                    'quantity' => floatval($value->product_qty),
                    'weight_in_gram' => $value->weight_g,
                    'note' => $value->note,
                    'store' => $value->store_name,
                    'store_domain' => $value->domain
                ]);

                $data[$value->store_id]['items'] = $items;

                $totalCart = floatval($totalCart) + (floatval($value->net_price) * floatval($value->product_qty));
            }
        }

        return response()->json([
            'success' => true,
            'message' => $cart ? 'Data found' : 'Cart is empty',
            'count_data' => count($cart),
            'data' => [
                // 'cart' => $cart ? CartResource::collection($cart) : null,
                'cart' => $cart ? collect(array_values($data)) : null,
                'total_cart' => $totalCart
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                /** Can't use exist rule since postgres need to convert field type to TEXT first (product_uuid field is UUID datatype)
                 * if you use mysql / mariadb OR product_uuid field is a VARCHAR datatype, exist rule should work
                */
                // 'product_uuid' => 'required|exists:App\Models\Product,product_uuid',

                'product_uuid' => 'required',
                'qty' => 'required|numeric|min:1',
                'note' => 'string|nullable|max:200'
            ], [
                'product_uuid.required' => 'Product UUID is required.',
                'product_uuid.exist' => 'Product with requested UUID not found.',

                'qty.required' => 'Product Quantity is required.',
                'qty.numeric' => 'Product Quantity must be numeric.',
                'qty.min' => 'Product Quantity must more than 0.',

                'note.string' => 'Value must be string.',
                'note.max' => 'Value must be string and cannot be more than 200 character.'
            ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $product = $this->productModel->findProduct($validator->validated()['product_uuid'])->first();
            if ($product) {
                $checkCartItem = $this->cartModel->getItem($validator->validated()['product_uuid'], auth()->guard('api')->user()->id)->first();
                if($checkCartItem) {
                    $updateData = [
                        'product_qty' => intval($checkCartItem->product_qty) + intval($validator->validated()['qty']),
                        'updated_at' => now(),
                        'updated_tz' => date_default_timezone_get()
                    ];

                    $inputCart = Cart::where('id', $checkCartItem->id)->update($updateData);
                } else {
                    $inputData = [
                        'product_uuid' => $validator->validated()['product_uuid'],
                        'product_qty' => $checkCartItem ? (intval($checkCartItem->product_qty) + intval($validator->validated()['qty'])) : intval($validator->validated()['qty']),
                        'store_id' => $product->store_id,
                        'note' => $validator->validated()['note'],
                        'user_id' => auth()->guard('api')->user()->id,
                        'created_at' => now(),
                        'created_tz' => date_default_timezone_get(),
                        'updated_at' => now(),
                        'updated_tz' => date_default_timezone_get()
                    ];

                    $inputCart = Cart::insert($inputData);
                }

                if ($inputCart) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Success add to cart'
                    ], 201);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed add to cart.'
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => ['product_uuid' => 'Product with requested UUID not found.']
                ], 400);
            }
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, Int $cartID)
    {
        if ($cartID) {
            $cart = $this->cartModel->checkCart($cartID, auth()->guard('api')->user()->id)->first();
            if ($cart) {
                $validator = Validator::make($request->all(), [
                        /** Can't use exist rule since postgres need to convert field type to TEXT first (product_uuid field is UUID datatype)
                         * if you use mysql / mariadb OR product_uuid field is a VARCHAR datatype, exist rule should work
                        */
                        // 'product_uuid' => 'required|exists:App\Models\Product,product_uuid',

                        'product_uuid' => 'required',
                        'qty' => 'required|numeric'
                    ], [
                        'product_uuid.required' => 'Product UUID is required.',
                        // 'product_uuid.exist' => 'Product with requested UUID not found.',

                        'qty.required' => 'Product Quantity is required.',
                        'qty.numeric' => 'Product Quantity must be numeric.'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    if ($cart->product_uuid === $validator->validated()['product_uuid']) {
                        $product = $this->productModel->findProduct($validator->validated()['product_uuid'])->first();
                        if ($product) {
                            if ($validator->validated()['qty'] > 0) {
                                $updateData = [
                                    'product_qty' => $validator->validated()['qty'],
                                    'updated_at' => now(),
                                    'updated_tz' => date_default_timezone_get()
                                ];

                                $updateCart = Cart::where('id', $cartID)->where('user_id', auth()->guard('api')->user()->id)->update($updateData);

                                if ($updateCart) {
                                    return response()->json([
                                        'success' => true,
                                        'message' => 'Success change item quantity'
                                    ], 200);
                                } else {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Failed to change item quantity'
                                    ], 500);
                                }
                            } else {
                                $deleteCart = Cart::where('id', $cartID)->where('user_id', auth()->guard('api')->user()->id)->delete();

                                if ($deleteCart) {
                                    return response()->json([
                                        'success' => true,
                                        'message' => 'Item removed from cart due to quantity is less than 1'
                                    ], 200);
                                } else {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Failed to remove item from cart'
                                    ], 500);
                                }
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => ['product_uuid' => 'Product with requested UUID not found.']
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => [
                                'product_uuid' => 'Product uuid doesn\'t match'
                            ]
                        ], 400);
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found!'
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }

    public function destroy(Int $cartID)
    {
        if ($cartID) {
            $cart = $this->cartModel->checkCart($cartID, auth()->guard('api')->user()->id)->first();
            if ($cart) {
                $deleteCart = Cart::where('id', $cartID)->where('user_id', auth()->guard('api')->user()->id)->delete();

                if ($deleteCart) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Item removed from cart'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to remove item from cart'
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found!'
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }
}
