<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Store;
use App\Models\UserAddress;
use App\Services\RajaOngkirService;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    protected $orderModel;
    protected $orderItemModel;
    protected $cartModel;
    protected $storeModel;
    protected $addressModel;
    protected $rajaOngkirService;

    public function __construct ()
    {
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->cartModel = new Cart();
        $this->storeModel = new Store();
        $this->addressModel = new UserAddress();
        $this->rajaOngkirService = new RajaOngkirService();
    }

    public function index(Request $request)
    {
        $dbTimezone = DB::selectOne('SELECT current_setting(\'TIMEZONE\') as tz')->tz; // selectOne will return one row as an object
        $filter = [];
        if ($request->from_date) {
            $filter['utc_start_date'] = Carbon::parse($request->from_date)->tz('UTC')->format('Y-m-d H:i:s');
            $filter['sys_start_date'] = Carbon::parse($request->from_date, date_default_timezone_get())->tz($dbTimezone)->format('Y-m-d H:i:s');
        }

        if ($request->to_date) {
            $filter['utc_finish_date'] = Carbon::parse($request->to_date)->tz('UTC')->format('Y-m-d H:i:s');
            $filter['sys_finish_date'] = Carbon::parse($request->to_date, date_default_timezone_get())->tz($dbTimezone)->format('Y-m-d H:i:s');
        }

        $orders = $this->orderModel->getUserOrder($filter, auth()->guard('api')->user()->id);

        return response()->json([
            'success' => true,
            'message' => $orders ? 'Data found' : 'No order data recorded',
            'from_date' => $request->from_date ? Carbon::parse($request->from_date)->tz('UTC')->format('c') : null,
            'to_date' => $request->to_date ? Carbon::parse($request->to_date)->tz('UTC')->format('c') : null,
            'count_data' => count($orders),
            'data' => $orders,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'cart' => 'required|array',
                'cart.*' => 'exists:App\Models\Cart,id',
                // 'discount_percent' => 'present|nullable|numeric|between:0,100.00',
                // 'discount_price' => 'present|nullable|numeric',
                'delivery_address' => 'required|exists:App\Models\UserAddress,id',
                'delivery_method' => ['required', Rule::in(["KS","DS"])],
                'delivery_courier' => 'required_if:delivery_method,DS|exists:App\Models\DeliveryCourier,id',
                'delivery_service' => 'required_if:delivery_method,DS',
                'payment_method' => ['required', Rule::in(["C","TF"])]
            ], [
                'cart.required' => 'Shopping Cart is required',
                'cart.*.exists' => 'Shopping Cart not found',
                // 'discount_percent.present' => 'Discount percent must be present but can be empty',
                // 'discount_percent.numeric' => 'Value must be numeric',
                // 'discount_price.present' => 'Discount price must be present but can be empty',
                // 'discount_price.numeric' => 'Value must be numeric',
                'delivery_address.required' => 'Delivery address is required',
                'delivery_address.exists' => 'Delivery address not found',
                'delivery_method.in' => 'Value must be "KS" for without using Expedition / Delivery Courier or "DS" for delivery using Expedition / Delivery Courier',
                'delivery_method.required' => 'Delivery method is required',
                'delivery_courier.required_if' => 'Please select Delivery Courier',
                'delivery_courier.exists' => 'Delivery Courier not found',
                'delivery_service.required_if' => 'Please select Delivery Service',
                'payment_method.required' => 'Payment method is required',
                'payment_method.in' => 'Value must be "C" for Cash or "TF" for Transfer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $cart = $this->cartModel->selectCarts(auth()->guard('api')->user()->id, $validator->validated()['cart']);
            if ($this->checkSameValue($cart->toArray(), 'store_id')) {
                // Get address for calculate delivery fee, ONLY IF delivery method is 'DS'
                $userAddress = $this->addressModel->findAddress($validator->validate()['delivery_address'], auth()->guard('api')->user()->id)->first();
                if ($userAddress) {
                    /** Data to insert
                     * Note:
                     * order_status:
                     *  'W' = Waiting for payment,
                     *  'A' = Pid and waiting for Payment Approval,
                     *  'S' = shipping / delivery process,
                     *  'F' = Order is complete
                     * payment_status: 0 = waiting for payment, 1 = paid
                    */
                    $inputData = [
                        'order_code' => 'UVX'.Carbon::now()->tz('UTC')->format('ymdHis').auth()->guard('api')->user()->id,
                        'order_date' => Carbon::now()->tz('UTC')->format('Y-m-d H:i:s'),
                        'order_status' => 'W',
                        'total_cart' => 0,
                        'total_weight_g' => 0,
                        'delivery_method' => $validator->validated()['delivery_method'],
                        'delivery_fee' => 0,
                        'delivery_courier_id' => $validator->validated()['delivery_method'] == 'DS' ? $validator->validated()['delivery_courier'] : null,
                        'delivery_service' => $validator->validated()['delivery_method'] == 'DS' ? $validator->validated()['delivery_service'] : null,
                        'delivery_etd' => '',
                        'delivery_note' => '',
                        'disc_percent' => 0,
                        'disc_price' => 0,
                        'total_payment' => 0,
                        'payment_method' => $validator->validated()['payment_method'],
                        'payment_status' => 0,
                        'due_date' => Carbon::now()->tz('UTC')->addDay()->format('Y-m-d H:i:s'),
                        'return_status' => 0,
                        'created_at' => now(),
                        'created_tz' => date_default_timezone_get(),
                        'updated_at' => now(),
                        'updated_tz' => date_default_timezone_get(),
                        'return_tz' => NULL,
                        'return_at' => NULL,
                        'store_id' => $cart->first()->store_id,
                        'user_id' => auth()->guard('api')->user()->id,
                        'user_address_id' => $validator->validate()['delivery_address']
                    ];
                    $inputItem = array();

                    // Calculate Sub-Total Cart & Total weight
                    foreach ($cart as $value) {
                        $inputData['total_cart'] = round(floatval($inputData['total_cart']) + (floatval($value->net_price) * floatval($value->product_qty)), 2);
                        $inputData['total_weight_g'] = $inputData['total_weight_g'] + ($value->weight_g * $value->product_qty);

                        array_push($inputItem, [
                            'order_code' => $inputData['order_code'],
                            'product_uuid' => $value->product_uuid,
                            'qty' => $value->product_qty,
                            'initial_price' => $value->initial_price,
                            'net_price' => $value->net_price,
                            'disc_percent' => $value->disc_percent,
                            'disc_price' => $value->disc_price,
                            'created_at' => now(),
                            'created_tz' => date_default_timezone_get(),
                            'updated_at' => now(),
                            'updated_tz' => date_default_timezone_get(),
                        ]);
                    }

                    // Calculate Delivery Fee
                    $storeAddress = $this->storeModel->findStore($cart->first()->store_id)->first();
                    if ($validator->validated()['delivery_method'] == 'DS') {
                        $services = $this->rajaOngkirService->getCosts($storeAddress->city_id, $userAddress->city_id, $inputData['total_weight_g'], 'jne');
                        if ($services['status']['code'] !== 200) {
                            return response()->json([
                                'success' => false,
                                'message' => $services['status']['description']
                            ], 400);
                        } else {
                            foreach ($services['results'][0]['costs'] as $value) {
                                if ($value['service'] == $validator->validated()['delivery_service']) {
                                    $inputData['delivery_fee'] = $value['cost'][0]['value'] ?? 0;
                                    $inputData['delivery_etd'] = $value['cost'][0]['etd'] ?? '';
                                    $inputData['delivery_note'] = $value['cost'][0]['note'] ?? '';
                                    break;
                                }
                            }
                        }
                    } else {
                        $inputData['delivery_fee'] = 0;
                    }

                    // Calculate Total Payment
                    $inputData['total_payment'] = floatval($inputData['total_cart']) + floatval($inputData['delivery_fee']);

                    $inputOrder = $this->orderModel->insertOrder($inputData);
                    if ($inputOrder) {
                        $inputItem = $this->orderItemModel->insertItem($inputItem);
                        if ($inputItem) {
                            // Delete Cart
                            $this->cartModel->deleteCart($validator->validated()['cart']);

                            return response()->json([
                                'success' => true,
                                'message' => 'Success make new order'
                            ], 201);
                        } else {
                            $this->orderModel->deleteOrder($inputData['order_code']);
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed to make new order, failed to save order item'
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to make new order'
                        ], 500);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => [
                            'delivery_address' => ["Delivery address not found"]
                        ]
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => [
                        'cart' => ["Cart must be from the same store for each order"]
                    ]
                ], 400);
            }
        }
    }

    public function show($code)
    {
        if ($code) {
            $order = $this->orderModel->findOrder($code)->first();
            if ($order) {
                if (auth()->guard('api')->user()->role == 'user' && auth()->guard('api')->user()->id !== $order->user_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found!'
                    ], 404);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order found!',
                        'data' => OrderResource::make($order)
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found!'
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide order number!'
            ], 400);
        }
    }

    /** Function to check if array key have the same value */
    protected function checkSameValue (Array $array, String $key) {
        $values = array_column($array, $key);
        return count(array_unique($values)) === 1;
    }
}
