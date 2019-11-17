<?php
namespace App\Repository;
use App\User;
class SettingsRepository
{
    public function updateUser($user_id, $data)
    {
        return User::where('id', $user_id)->update($data);
    }

    public function checkEmailExist($user_id, $email)
    {
        return User::where('id','<>', $user_id)->where('email', $email)->exists();
    }

    public function checkPhoneNumber($user_id, $number)
    {
        return User::where('id','<>', $user_id)->where('phone_number', $number)->exists();
    }

    public function checkLicenseNumber($user_id, $license_number)
    {
        return User::where('id','<>', $user_id)->where('license_number', $license_number)->exists();
    }
}
