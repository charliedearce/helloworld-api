<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class BookingController extends BaseController
{
    public function therapistStatus(Request $request)
    {
        $rs = $this->bookingRepository;
        if($rs->getAvailableTherapistById($this->user_info->id)){
            $res = $rs->deleteAvailableTherapist($this->user_info->id);
            if($res){
                return response()->json(['message' => 'status not active'], 200);
            } else {
                return response()->json(['message' => 'something went wrong'], 200);
            }
        } else {
            $fields = [
                'casino' => $request->input('casino'),
                'currency'=> $request->input('currency'),
                'rate'=> $request->input('rate'),
                'specials'=> $request->input('specials'),
            ];

            $conditions = [
                'casino' => 'required',
                'currency'=>'required|in:USD,EUR',
                'rate' => 'required|integer',
                'specials' => 'required',
            ];

            $validator = Validator::make($fields, $conditions);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all()], 200);
            }

            $fields['user_id'] = $this->user_info->id;

            $res = $rs->insertAvailableTherapist($fields);
            if($res){
                return response()->json(['message' => 'status active'], 200);
            } else {
                return response()->json(['message' => 'something went wrong'], 200);
            }
        }
    }

    public function getTherapistStatusInfos()
    {
        return $this->bookingRepository->getAvailableTherapistById($this->user_info->id);
    }

    public function getTherapistList()
    {
        $name = isset($_GET['name']) ? $_GET['name'] : '';
        $rs = $this->bookingRepository;
        $res = [];
        $data = $rs->getAvailableTherapist($name);
        foreach ($data as $k => $d){
            $res[$k]['id'] = $d->id;
            $res[$k]['name'] = $d->first_name . " " . $d->last_name;
            $res[$k]['image'] = $d->image;
            $res[$k]['casino'] = $d->casino;
            $res[$k]['currency'] = $d->currency === 'USD' ? '$' : '€';
            $res[$k]['rate'] = $d->rate;
            $res[$k]['specials'] = $d->specials;
            $res[$k]['busy'] = $rs->getTherapistStatus($d->user_id);
        }

        return $res;
    }
}
