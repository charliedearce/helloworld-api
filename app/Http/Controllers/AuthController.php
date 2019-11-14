<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends BaseController
{
    public function registerClient(Request $request)
    {
        $fields = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email'=> $request->input('email'),
            'password'=> $request->input('password'),
            'c_password' => $request->input('c_password'),
        ];

        $conditions = [
            'first_name' => 'required|alpha_spaces',
            'last_name' => 'required|alpha_spaces',
            'email'=>'email|required|unique:users',
            'password'=>'required|min:6',
            'c_password' => 'required|same:password',
        ];

        $validator = Validator::make($fields, $conditions);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }

        $fields['password'] = Hash::make($fields['password']);

        $fields['type'] = 1;

        $rs = $this->authRepository;

        $user = $rs->createAccount($fields);

        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['access_token'=> $accessToken]);
    }

    public function registerClientSocial(Request $request)
    {
        $fields = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email'=> $request->input('email'),
            'social_id'=> $request->input('social_id'),
        ];

        $conditions = [
            'first_name' => 'required|alpha_spaces',
            'last_name' => 'required|alpha_spaces',
            'email'=>'email|required|email',
            'social_id' => 'required|numeric',
        ];

        $validator = Validator::make($fields, $conditions);
        $accessToken = '';
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }
        $fields['password'] = Hash::make($fields['social_id']);
        $fields['type'] = 1;

        $rs = $this->authRepository;
        $data = $rs->checkEmailExists($fields['email']);

        if(!empty($data)){
            if($data->social_id === null){
                 $res = $rs->updateUserByEmail($data->email, ['social_id' => $fields['social_id'], 'password' => $fields['password']]);
                if($res) {
                    $data = [
                        'grant_type' => 'password',
                        'client_id' => '2',
                        'client_secret' => '5W70zakBjjwhC4Ea36IS7JFzJU6naVE0Wa76ap4y',
                        'username' => $fields['email'],
                        'password' => $fields['social_id'],
                        'scope' => '*',
                    ];

                    $accessToken = json_decode($this->curlPOST(ENV('APP_URL').'/oauth/token', $data))->access_token;
                }
            } else {
                $data = [
                    'grant_type' => 'password',
                    'client_id' => '2',
                    'client_secret' => '5W70zakBjjwhC4Ea36IS7JFzJU6naVE0Wa76ap4y',
                    'username' => $fields['email'],
                    'password' => $fields['social_id'],
                    'scope' => '*',
                ];

                $accessToken = json_decode($this->curlPOST(ENV('APP_URL').'/oauth/token', $data))->access_token;
            }
        } else {
            $user = $rs->createAccount($fields);
            $accessToken = $user->createToken('authToken')->accessToken;
        }


        return response(['access_token'=> $accessToken]);

    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $data = [
                'grant_type' => 'password',
                'client_id' => '2',
                'client_secret' => '5W70zakBjjwhC4Ea36IS7JFzJU6naVE0Wa76ap4y',
                'username' => $loginData['email'],
                'password' => $loginData['password'],
                'scope' => '*',
            ];

        $accessToken = json_decode($this->curlPOST(ENV('APP_URL').'/oauth/token', $data));

        return response(['access_token'=> $accessToken]);
    }

    public function details()
    {
        return response()->json(['success' => $this->user_info]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'successfully logged out'
        ]);
    }

    public function registerTherapist(Request $request)
    {
        $fields = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email'=> $request->input('email'),
            'password'=> $request->input('password'),
            'c_password' => $request->input('c_password'),
            'license_number' => $request->input('c_password'),
            'license_state' => $request->input('c_password'),
            'phone_number' => $request->input('c_password'),
            'company' => $request->input('company'),
        ];

        $conditions = [
            'first_name' => 'required|alpha_spaces',
            'last_name' => 'required|alpha_spaces',
            'email'=>'email|required|unique:users',
            'password'=>'required|min:6',
            'c_password' => 'required|same:password',
            'license_number' => 'required',
            'license_state' => 'required',
            'phone_number' => 'required',
            'company' => 'required',
        ];

        $validator = Validator::make($fields, $conditions);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }

        $fields['password'] = Hash::make($fields['password']);

        $fields['type'] = 2;

        $rs = $this->authRepository;

        $user = $rs->createAccount($fields);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['access_token'=> $accessToken]);
    }
}
