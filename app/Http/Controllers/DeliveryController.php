<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DeliveryCourier;
use App\Http\Resources\DeliveryResource;

class DeliveryController extends Controller
{
    protected $deliveryModel;
    protected $storeDSModel;
    protected $rules;
    protected $messages;

    public function __construct()
    {
        $this->deliveryModel = new DeliveryCourier();
        $this->rules = [
            'name' => 'required',
            'api_code' => 'required'
        ];
        $this->messages = [
            'name.required' => 'Delivery Name is required',
            'api_code.required' => 'Code is required'
        ];
    }

    public function index(Request $request)
    {
        $provider = $this->deliveryModel->getServices($request->search);

        return response()->json([
            'success' => true,
            'count_data' => count($provider),
            'dataS' => $provider ? DeliveryResource::collection($provider) : null,
            'data' => $provider
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $inputData = [
                'name' => $validator->validated()['name'],
                'ro_api_param' => $validator->validated()['api_code'],
                'created_at' => now(),
                'created_tz' => date_default_timezone_get(),
                'updated_at' => now(),
                'updated_tz' => date_default_timezone_get()
            ];

            $inputDelivery = DeliveryCourier::insert($inputData);
            if ($inputDelivery) {
                return response()->json([
                    'success' => true,
                    'message' => 'Success add new delivery service'
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed add new delivery service'
                ], 500);
            }
        }
    }

    public function show(Int $id)
    {
        if ($id) {
            $provider = $this->deliveryModel->findServices($id);

            if ($provider) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data found',
                    'data' => DeliveryResource::collection($provider)
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data with provided "id" not found'
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        if ($id) {
            $provider = DeliveryCourier::find($id);

            if ($provider) {
                $validator = Validator::make($request->all(), $this->rules, $this->messages);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The given data was invalid',
                        'errors' => $validator->errors()
                    ], 400);
                } else {
                    $updateData = [
                        'name' => $validator->validated()['name'],
                        'ro_api_param' => $validator->validated()['api_code'],
                        'updated_at' => now(),
                        'updated_tz' => date_default_timezone_get()
                    ];

                    $updateDelivery = DeliveryCourier::where('id', $id)->update($updateData);
                    if ($updateDelivery) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Success change delivery service data'
                        ], 201);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed change delivery service data'
                        ], 500);
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data with provided "id" not found'
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide parameter "id"!'
            ], 400);
        }
    }

    public function destroy($id)
    {
        if ($id) {
            $provider = DeliveryCourier::find($id);

            if ($provider) {
                $deleteService = DeliveryCourier::where('id', $id)->delete();

                if ($deleteService) {
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
                    'success' => false,
                    'message' => 'Data with provided "id" not found'
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
