<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userBookings = Auth::user()->trips()->paginate();
        return response()->json(['status' => 'success', 'userBookings' => $userBookings]);
    }

    public function makeReservation($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        if($trip) {//trip exists
            $booking = \DB::table('booking')->where('user_id', Auth::id())->where('trip_id', $trip->id)->exists();
            if($booking) {//already booked
                return response()->json(['status' => 'fail']);
            }
            $inserted = \DB::table('booking')->insert([
                'user_id' => Auth::id(),
                'trip_id' => $trip->id,
                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ]);
            return response()->json(['status' => $inserted ? 'success' : 'fail']);
        }
        return response()->json(['status' => 'fail']);
    }

    public function cancelReservation($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        if($trip) {//trip exists
            $booking = \DB::table('booking')->where('user_id', Auth::id())->where('trip_id', $trip->id)->exists();
            if(!$booking) {//not booked by authenticated user
                return response()->json(['status' => 'fail']);
            }
            else {
                $canceled = \DB::table('booking')->where('user_id', Auth::id())->where('trip_id', $trip->id)->delete();
                return response()->json(['status' => $canceled ? 'success' : 'fail']);
            }
        }
        return response()->json(['status' => 'fail']);
    }

}
