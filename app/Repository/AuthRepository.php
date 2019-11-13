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
}
