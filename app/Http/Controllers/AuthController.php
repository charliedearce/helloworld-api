<?php

namespace App\Http\Controllers;

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

        unset($fields['c_password']);

        $rs = $this->authRepository;

        $user = $rs->createAccount($fields);

        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['access_token'=> $accessToken,'type' => $user->type]);
    }

    public function registerClientSocial(Request $request)
    {
        $fields = [
            'display_name' => $request->input('display_name'),
            'email'=> $request->input('email'),
            'social_id'=> $request->input('social_id'),
            'image'=> $request->input('image'),
        ];

        $conditions = [
            'display_name' => 'required',
            'email'=>'email|required|email',
            'social_id' => 'required',
            'image' => 'nullable|url',
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
            if(empty($data->social_id)){
                 $res = $rs->updateUserByEmail($data->email, ['social_id' => $fields['social_id']]);
                if($res) {
                    $user = $rs->checkEmailSocialId($data->email, $fields['social_id']);
                    $accessToken = $user->createToken('authToken')->accessToken;
                } else {
                    return response(['message'=> 'unauthorized']);
                }
            } else {
                $user = $rs->checkEmailSocialId($data->email, $fields['social_id']);
                if($user){
                    $accessToken = $user->createToken('authToken')->accessToken;
                } else {
                    return response(['message'=> 'unauthorized']);
                }
            }
        } else {
            $user = $rs->createAccount($fields);
            $accessToken = $user->createToken('authToken')->accessToken;
        }


        return response(['access_token'=> $accessToken, 'type' => $user->type]);

    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        $data = [
                'grant_type' => 'password',
                'client_id' => ENV('AUTH_ID'),
                'client_secret' => ENV('AUTH_KEY'),
                'username' => $loginData['email'],
                'password' => $loginData['password'],
                'scope' => '*',
            ];

        $accessToken = json_decode($this->curlPOST(ENV('APP_URL').'/oauth/token', $data));

        if(!isset($accessToken->access_token)){
            return response(['message'=> 'unauthorized']);
        }

        $user = $this->authRepository->checkEmailExists($loginData['email']);

        return response(['access_token'=> $accessToken->access_token, 'type' => $user->type]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        $this->bookingRepository->deleteAvailableTherapist($this->user_info->id);
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
            'license_number' => $request->input('license_number'),
            'license_state' => $request->input('license_state'),
            'phone_number' => $request->input('phone_number'),
            'company' => $request->input('company'),
        ];

        $conditions = [
            'first_name' => 'required|alpha_spaces',
            'last_name' => 'required|alpha_spaces',
            'email'=>'email|required|unique:users',
            'password'=>'required|min:6',
            'c_password' => 'required|same:password',
            'license_number' => 'required',
            'license_state' => 'required|alpha_spaces',
            'phone_number' => 'required|numeric',
            'company' => 'required',
        ];

        $validator = Validator::make($fields, $conditions);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }

        $rs = $this->authRepository;

        if($rs->checkPhoneNumber($fields['phone_number']) === true){
            return response()->json(['message' => 'phone number already exists'], 200);
        }

        if($rs->checkLicenseNumber($fields['license_number']) === true){
            return response()->json(['message' => 'license number already exists'], 200);
        }


        $fields['password'] = Hash::make($fields['password']);

        $fields['type'] = 2;

        unset($fields['c_password']);

        $user = $rs->createAccount($fields);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['access_token'=> $accessToken, 'type' => 2]);
    }
}
