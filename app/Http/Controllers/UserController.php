<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Registration;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use App\Mail\AuthMail;

class UserController extends Controller
{
    protected $userModel;
    protected $addressModel;
    protected $cityModel;
    protected $districtModel;
    protected $registrationModel;

    public function __construct() {
        $this->userModel = new User();
        $this->addressModel = new UserAddress();
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->registrationModel = new Registration();
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

    /** Input in one go (all email, name, and password in one request) */
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

    /** User input one time in each request,
     * 1. First step is email / phone validation, Client post an email, this function will return an OTP using SMTP
     * 2. Second step is email / phone OTP validation in "verifyOTP" function
     * 3. Third step is client input name and password to "userRegistration" function
     * About phone regex, after reading article in stackoverflow, i think this 2 regex will work similiarly
     * A: ^([0|\+[1-9]{1,5})?([0-9]{10})$
     * A Regex Explaination:
     * [0|\+[1-9]{1,5} : match single char 0 or +, match perviouse char / set (char set is digit 1-9) for 1 - 5 times
     * [0-9]{10} : match any digit from 0-9 for exactly 10 times
     * this mean max length of value is 15 character and min length is 10 character start with 0 and 11 character start with +
     * B: ^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$
     * (0[1-9]{1} : match single char 0 and followed by digit between 1-9 for 1 time
     * | : determine "or"
     * (\+[1-9]) : match char + and followed any digit from 1-9 for exactly 1 times
     * [0-9]{3, 13} : match any digit 0-9 for 3 time to 13 time
     * this mean max length of value is 15 character and min length is 4 character whether it start with 0 or with +
     *
     * A regex: value start with "00" still work, ex: "001" is still work
     * B regex: value start with "00" wont work, ex: "001" will not work
     * but, value start with "+0" wont work, ex: +01 will not work
     */
    public function registration (Request $request) {
        $validator = Validator::make($request->all(), [
                'email' => 'required_without:phone|nullable|email:dns|unique:App\Models\User',
                'phone' => ['required_without:email', 'nullable', 'regex:/^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$/', 'min:8', 'unique:App\Models\User'],
            ],
            [
                'email.required_without' => 'Email / Phone number is required',
                'email.email' => 'Email is invalid',
                'email.unique' => 'Email is registered'

                // 'phone.required_without' => 'Email or Mobilephone Number must be filled',
                // 'phone.min' => 'Mobilephone Number must ber at least 8 character (including country prefix)',
                // 'phone.regex' => 'Mobilephone Number is invalid. Allowed characted : + and 0-9; without space, "-", or "\"; Starting with "00" is not allowed use +(prefix) instead',
                // 'phone.unique' => 'Mobilephone Number is already registered',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $validator->errors()
            ], 400);
        } else {
            // $uncompletedRegister = $this->registrationModel->findRegistration(['email' => $validator->validated()['email'] ?? null, 'phone' => $validator->validated()['phone'] ?? null])->first();
            $otp = self::createOTP(6);
            $data = [
                'email' => $validator->validated()['email'] ?? null,
                'phone' => $validator->validated()['phone'] ?? null,
                'otp' => Hash::make($otp),
                'valid_until' => Carbon::now()->addMinute(5),
                'valid_tz' => date_default_timezone_get(),
                'created_at' => now(),
                'created_tz' => date_default_timezone_get(),
                'updated_at' => now(),
                'updated_tz' => date_default_timezone_get()
            ];

            $upsert = $this->registrationModel->inputRegistration($data);
            if ($upsert) {
                Mail::to('fake@mail.com')->send(new AuthMail(['otp' => $otp, 'valid_until' => $data['valid_until'], 'valid_tz' => $data['valid_tz']]));

                return response()->json([
                    'success' => true,
                    'message' => 'Registration success. OTP is send to email / phone.',
                    'otp_valid' => Carbon::parse($data['valid_until'])->format('c')
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'System error. Failed to save data to database.'
                ], 500);
            }
        }
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

    protected function createOTP($length) {
        $charSet = '0123456789';
        $otp = '';
        $charSetLength = strlen($charSet);

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charSetLength - 1);
            $otp .= $charSet[$index];
        }

        return $otp;
    }
}
