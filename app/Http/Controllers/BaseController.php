<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\AuthRepository;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected $authRepository;

    protected $user_info;

    public function __construct(
        Request $request,
        AuthRepository $authRepository
    )
    {
        $this->authRepository = $authRepository;
        $this->now = date("Y-m-d H:i:s");
        $this->user_info = Auth::user();
    }

    public function curlPOST($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
