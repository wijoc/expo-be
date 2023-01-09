<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __construct() {
        $this->user = new User();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->user->getUsers();

        if ($users) {
            return response()->json([
                'success' => true,
                'error' => false,
                'count_data' => UserResource::collection($users)->count(),
                'count_all' => $this->user->countAll()[0]->count_all,
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'name' => 'required|min:1|max:50',
                'email' => 'required_without:phone|nullable|email:dns|unique:App\Models\User',
                'phone' => ['required_without:email', 'nullable', 'regex:/^(0[1-9]{1}|(\+[1-9]{1}))[0-9]{3,13}$/', 'min:8', 'unique:App\Models\User'],
                'password' => 'required|min:8|max:16',
                'role' => ['required', Rule::in(["su", "admin", "user"])]
            ],
            [
                'name.required' => 'Name is required',
                'name.min' => 'Name must be at least 1 characters',
                'name.max' => 'Name cannot be more than 50 characters',

                'email.required_without' => 'Email or Mobilephone Number must be filled',
                'email.email' => 'Email is invalid',
                'email.unique' => 'Email is already registered',

                'phone.required_without' => 'Email or Mobilephone Number must be filled',
                'phone.min' => 'Mobilephone Number must ber at least 8 character (including country prefix)',
                'phone.regex' => 'Mobilephone Number is invalid. Allowed characted : + and 0-9; without space, "-", or "\"; Starting with "00" is not allowed use +(prefix) instead',
                'phone.unique' => 'Mobilephone Number is already registered',

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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
