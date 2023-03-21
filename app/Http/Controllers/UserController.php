<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use App\Models\UserAddress;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    protected $userModel;
    protected $addressModel;
    protected $cityModel;
    protected $districtModel;

    public function __construct() {
        $this->userModel = new User();
        $this->addressModel = new UserAddress();
        $this->cityModel = new City();
        $this->districtModel = new District();
    }

    public function index()
    {
        $users = $this->userModel->getUsers();

        if ($users) {
            return response()->json([
                'success' => true,
                'error' => false,
                'count_data' => UserResource::collection($users)->count(),
                'count_all' => $this->userModel->countAll()[0]->count_all,
                'data' => UserResource::collection($users)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'No data available!'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'name' => 'required|min:1|max:50',
                'email' => 'required|nullable|email:dns|unique:App\Models\User',
                // 'phone' => ['required_without:email', 'nullable', 'regex:/^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$/', 'min:8', 'unique:App\Models\User'],
                'password' => 'required|confirmed|min:8|max:16',
                'role' => ['required', Rule::in(["su", "admin", "user"])]
            ],
            [
                'name.required' => 'Name is required',
                'name.min' => 'Name must be at least 1 characters',
                'name.max' => 'Name cannot be more than 50 characters',

                'email.required' => 'Email is required',
                'email.email' => 'Email is invalid',
                'email.unique' => 'Email is already registered',

                // 'phone.required_without' => 'Email or Mobilephone Number must be filled',
                // 'phone.min' => 'Mobilephone Number must ber at least 8 character (including country prefix)',
                // 'phone.regex' => 'Mobilephone Number is invalid. Allowed characted : + and 0-9; without space, "-", or "\"; Starting with "00" is not allowed use +(prefix) instead',
                // 'phone.unique' => 'Mobilephone Number is already registered',

                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 character',
                'password.max' => 'Password cannot be more than 16 characters'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $validated = $validator->validated();
            $validated['password'] = Hash::make($validated['password']);
            $inputUser = User::insert($validated);

            if ($inputUser) {
                return response()->json([
                    'success' => true,
                    'error' => false,
                    'message' => 'Success add new data'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => false,
                    'message' => 'Failed add new data'
                ], 400);
            }
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function userRegister (Request $request) {
        $validator = Validator::make($request->all(), [
                'name' => 'required|min:1|max:50',
                'email' => 'required|nullable|email:dns|unique:App\Models\User',
                // 'phone' => ['required_without:email', 'nullable', 'regex:/^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$/', 'min:8', 'unique:App\Models\User'],
                'password' => 'required|confirmed|min:8',
                'image' => 'image|file|max:2048',
            ],
            [
                'name.required' => 'Name is required',
                'name.min' => 'Name must be at least 1 characters',
                'name.max' => 'Name cannot be more than 50 characters',
                'email.required' => 'Email is required',
                'email.email' => 'Email is invalid',
                'email.unique' => 'Email is already registered',

                // 'phone.required_without' => 'Email or Mobilephone Number must be filled',
                // 'phone.min' => 'Mobilephone Number must ber at least 8 character (including country prefix)',
                // 'phone.regex' => 'Mobilephone Number is invalid. Allowed characted : + and 0-9; without space, "-", or "\"; Starting with "00" is not allowed use +(prefix) instead',
                // 'phone.unique' => 'Mobilephone Number is already registered',

                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 character',
                'image.image' => 'File must be an image (jpg, jpeg, png, bmp, gif, svg, or webp)',
                'image.max' => 'File size can not be greater than 2MB (2048 KB)',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $validated = [
                'email' => $validator->validated()['email'],
                // 'email_prefix' => ,
                // 'phone' => $validator->validated()['phone'],
                // 'phone_prefix' => ,
                'password' => Hash::make($validator->validated()['password']),
                'image_path' => $request->file('image')->store('user-profiles'),
                'image_mime' => $request->file('image')->getMimeType(),
                'role' => 'user'
            ];

            $inputUser = User::insert($validated);

            if ($inputUser) {
                return response()->json([
                    'success' => true,
                    'errors' => false,
                    'message' => 'Success add new data'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'errors' => false,
                    'message' => 'Failed add new data'
                ], 500);
            }
        }
    }

    public function login (Request $request) {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ],
            [
                'email.required' => 'Email is required',
                'email.email' => 'Email is invalid',
                'password.required' => 'Password is required'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $credentials = $request->only(['email', 'password']);

            if (! $jwtoken = auth()->guard('api')->claims(['type' => 'access_token'])->setTTL(31536000)->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'The given data was invalid',
                    'errors' => [
                        'credentials' => "Email / Password is incorect"
                    ]
                ], 400);
            }

            return response()->json([
                'success' => true,
                'error' => false,
                'message' => 'Login success',
                'access_token' => $jwtoken,
                'refresh_token' => auth()->guard('api')->claims(['type' => 'refresh_token'])->setTTL(36000)->attempt($credentials)
            ], 200);
        }
    }

    public function refreshToken () {
        try {
            $payload = auth()->guard('api')->payload();

            if ($payload->get('type') === 'refresh_token') {
                // auth()->invalidate(); // Invalidate refresh token

                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'The given data was invalid',
                    'access_token' => auth()->guard('api')->claims(['type' => 'access_token'])->refresh(),
                    'refresh_token' => auth()->guard('api')->claims(['type' => 'refresh_token'])->setTTL(36000)->refresh()
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh Token is Invalid'
                ], 401);
            }
        }
        catch (JWTException $error) {
            if ($error instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh Token is Invalid'
                ], 401);
            }else if ($error instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh Token is Expired'
                ], 401);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization Token not found'
                ], 401);
            }
        }
    }

    public function userProfile (Request $request) {
        return response()->json([
            'success' => true,
            'data' => UserResource::make(auth()->guard('api')->user())
        ], 200);
    }

    public function storeAddress (Request $request) {
        $validator = Validator::make($request->all(),
            [
                'recipient_name' => 'required|max:50',
                'province' => 'required|numeric|exists:App\Models\Province,id',
                'city' => 'required|numeric|exists:App\Models\City,id',
                'district' => 'required|numeric|exists:App\Models\District,id',
                'full_address' => 'required|max:200',
                'postal' => 'required',
                'note' => 'max:200'
            ],
            [
                'recipient_name.requried' => 'Recipient\'s Name is required',
                'recipient_name.max' => 'Recipient\'s Name can not be more than 50 character',
                'province.required' => 'Province is required',
                'province.numeric' => 'Value must be numeric',
                'province.exists' => 'Province with requested ID not found',
                'city.required' => 'City is required',
                'city.numeric' => 'Value must be numeric',
                'city.exists' => 'City with requested ID not found',
                'district.required' => 'Distrcit is required',
                'district.numeric' => 'Value must be numeric',
                'district.exists' => 'Distrcit with requested ID not found',
                'full_address.required' => 'Full address is required',
                'full_address.max' => 'Full address can not be more than 200 character',
                'postal.required' => 'Postal code is required',
                'note.max' => 'Note can not be more than 200 character'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            $validateCity = $this->cityModel->checkCity($validator->validated()['city'], $validator->validated()['province'])->count();
            $validateDistrict = $this->districtModel->checkDistrict($validator->validated()['district'], $validator->validated()['city'])->count();

            if ($validateCity < 1 || $validateDistrict < 1) {
                $validateCity < 1 ? $errors['city'][] = "City not in the selected province" : '';
                $validateDistrict < 1 ? $errors['district'][] = "District not in the selected city" : '';

                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid',
                    'errors' => $errors
                ], 400);
            } else {
                $address = $this->addressModel->getAllAddress(auth()->guard('api')->user()->id)->count();

                $inputData = [
                    'user_id' => auth()->guard('api')->user()->id,
                    'recipient_name' => $validator->validated()['recipient_name'],
                    'province_id' => $validator->validated()['province'],
                    'city_id' => $validator->validated()['city'],
                    'district_id' => $validator->validated()['district'],
                    'full_address' => $validator->validated()['full_address'],
                    'postal_code' => $validator->validated()['postal'],
                    'status' => $address > 0 ? 'D' : 'A',
                    'note' => $validator->validated()['note'],
                    'created_at' => now(),
                    'created_tz' => date_default_timezone_get(),
                    'updated_at' => now(),
                    'updated_tz' => date_default_timezone_get()
                ];

                $inputAddress = $this->addressModel->insertAddress($inputData);

                if ($inputAddress) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Success adding new address'
                    ], 201);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Failed adding new address'
                    ], 500);
                }
            }
        }
    }
}
