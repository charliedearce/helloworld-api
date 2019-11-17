<?php
namespace App\Repository;
use App\User;
class AuthRepository
{
    public function createAccount($data)
    {
        return User::create($data);
    }

    public function checkEmailExists($email)
    {
        return User::where('email', $email)->first();
    }

    public function updateUserByEmail($email, $data)
    {
        return User::where('email',$email)->update($data);
    }

    public function checkEmailSocialId($email, $social_id)
    {
        return User::where('email', $email)->where('social_id', $social_id)->first();
    }

    public function checkPhoneNumber($number)
    {
        return User::where('phone_number', $number)->exists();
    }

    public function checkLicenseNumber($license_number)
    {
        return User::where('license_number', $license_number)->exists();
    }

}
