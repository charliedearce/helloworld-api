<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Repository\AuthRepository;
use App\Repository\SettingsRepository;

class BaseController extends Controller
{
    protected $authRepository;
    protected $settingsRepository;

    protected $user_info;

    public function __construct(
        Request $request,
        AuthRepository $authRepository,
        SettingsRepository $settingsRepository
    )
    {
        $this->authRepository = $authRepository;
        $this->settingsRepository = $settingsRepository;

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
