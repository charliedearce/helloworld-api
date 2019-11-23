<?php


namespace App\Repository;
use App\Available_therapist;
use App\Booking;
use App\User;

class BookingRepository
{
    public function insertAvailableTherapist($data)
    {
        return Available_therapist::insert($data);
    }

    public function deleteAvailableTherapist($therapist_id)
    {
        Booking::where('therapist_user_id', $therapist_id)->where('cater', 0)->delete();
        return Available_therapist::where('user_id', $therapist_id)->delete();
    }

    public function getAvailableTherapistById($user_id)
    {
        return Available_therapist::where('user_id', $user_id)->first();
    }

    public function getAvailableTherapist($name = '')
    {
        $data = Available_therapist::join('users', 'available_therapists.user_id', 'users.id')
            ->whereRaw('lower(concat(users.first_name, \' \', users.last_name)) like lower(?)', ["%{$name}%"])
            ->get();
        if(empty($data)){
            $data = Available_therapist::join('users', 'available_therapists.user_id', 'users.id')
                ->whereRaw('lower(users.display_name) like lower(?)', ["%{$name}%"])
                ->get();
        }

        return $data;
    }

    public function getTherapistInfos($user_id)
    {
        return User::where('id', $user_id)->first();
    }

    public function getTherapistStatus($user_id)
    {
        return Booking::where('therapist_user_id', $user_id)->where('cater', 1)->exists();
    }
}
