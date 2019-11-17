<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SettingsController extends BaseController
{
    public function profileDetails()
    {
        return response()->json(['success' => $this->user_info]);
    }

    public function updateProfile(Request $request)
    {
        if($this->user_info->type === 2) {
            $fields = [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email'=> $request->input('email'),
                'license_number' => $request->input('license_number'),
                'license_state' => $request->input('license_state'),
                'phone_number' => $request->input('phone_number'),
                'company' => $request->input('company'),
                'image' => $request->input('image'),
                'bio' => $request->input('bio'),
            ];

            $conditions = [
                'first_name' => 'required|alpha_spaces',
                'last_name' => 'required|alpha_spaces',
                'email'=>'email|required',
                'license_number' => 'required',
                'license_state' => 'required|alpha_spaces',
                'phone_number' => 'required|numeric',
                'company' => 'required',
                'image' => 'nullable|url',
                'bio' => 'required',
            ];
        } else {
            $fields = [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'fave_game' => $request->input('fave_game'),
                'fave_casino' => $request->input('fave_casino'),
                'email'=> $request->input('email'),
                'phone_number'=> $request->input('phone_number'),
                'image' => $request->input('image'),
            ];

            $conditions = [
                'first_name' => 'required|alpha_spaces',
                'last_name' => 'required|alpha_spaces',
                'fave_game' => 'required|alpha_spaces',
                'fave_casino' => 'required|alpha_spaces',
                'email'=>'email|required',
                'phone_number'=>'numeric|required',
                'image' => 'nullable|url',
            ];
        }

        $validator = Validator::make($fields, $conditions);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }

        $rs = $this->settingsRepository;

        if($rs->checkEmailExist($this->user_info->id, $fields['email']) === true){
            return response()->json(['message' => 'email already exists'], 200);
        }

        if($rs->checkPhoneNumber($this->user_info->id, $fields['phone_number']) === true){
            return response()->json(['message' => 'phone number already exists'], 200);
        }

        if($this->user_info->type === 2) {
            if($rs->checkLicenseNumber($this->user_info->id, $fields['license_number']) === true){
                return response()->json(['message' => 'license number already exists'], 200);
            }
        }

        $res = $rs->updateUser($this->user_info->id, $fields);

        if($res){
            return response()->json(['message' => 'profile successfully updated'], 200);
        } else {
            return response()->json(['message' => 'nothing to update'], 200);
        }
    }

    public function uploadImage(Request $request)
    {
        $fields = [
            'image' => $request->file('image'),
        ];

        $conditions = [
            'image' => 'required|image',
        ];

        $validator = Validator::make($fields, $conditions);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }

        $user = (object)['image' => ""];

        $original_filename = $fields['image']->getClientOriginalName();

        $original_filename_arr = explode('.', $original_filename);

        $file_ext = end($original_filename_arr);

        $destination_path = './upload/user/';

        $image = Str::random(8) . time() . '.' . $file_ext;

        if ($fields['image']->move($destination_path, $image)) {
            $user->image = '/upload/user/' . $image;
            return response()->json(['image' => ENV('APP_URL') . $user->image], 200);
        } else {
            return response()->json(['message' => 'Cannot upload file'], 200);
        }
    }

    public function changePassword(Request $request)
    {
        $fields = [
            'password' => $request->input('password'),
            'new_password' => $request->input('new_password'),
            'c_password' => $request->input('c_password'),
        ];

        $conditions = [
            'password' => 'required',
            'new_password' => 'required',
            'c_password' => 'required|same:new_password',
        ];

        $validator = Validator::make($fields, $conditions);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 200);
        }

        $rs = $this->settingsRepository;

        if(Hash::check($fields['password'], $this->user_info->getAuthPassword())){
            $res = $rs->updateUser($this->user_info->id, [
                'password' => Hash::make($fields['c_password'])
            ]);

            if($res){
                return response()->json(['message' => 'password updated'], 200);
            } else {
                return response()->json(['message' => 'nothing to updated'], 200);
            }
        } else {
            return response()->json(['message' => 'wrong password'], 200);
        }
    }

}
