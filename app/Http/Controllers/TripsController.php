<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripsController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $searching = $request->all();
        $trips = Trip::orderBy(@$searching['order_by'] ? $searching['order_by'] : 'created_at', @$searching['order_type'] ? $searching['order_type'] : 'asc');
        if(isset($searching['start_date'])) {
            $trips->where('start_date', '>=', $searching['start_date']);
        }
        if(isset($searching['end_date'])) {
            $trips->where('end_date', '<=', $searching['end_date']);
        }
        if(isset($searching['price_from'])) {
            $trips->where('price', '>=', $searching['price_from']);
        }
        if(isset($searching['price_to'])) {
            $trips->where('price', '<=', $searching['price_to']);
        }
        if(isset($searching['keyword'])) {
            $trips->where(function ($q) use ($searching){
                $q->where('title', 'like', '%'.$searching['keyword'].'%')
                    ->orWhere('description', 'like', '%'.$searching['keyword'].'%')
                    ->orWhere('slug', 'like', '%'.$searching['keyword'].'%');
            });
        }
        $data = $trips->get();

        return response()->json(['status' => 'success', 'trips' => $data]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'slug' => 'required',
            'title' => 'required',
            'location' => 'required',
            'price' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $added = Trip::insert($request->all());
        if($added){
            return response()->json(['status' => 'success']);
        }else {
            return response()->json(['status' => 'fail']);
        }
    }

    public function show($id)
    {
        $trip = Trip::where('id', $id)->first();
        return response()->json($trip);
    }

    public function showBySlug($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        return response()->json($trip);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'slug' => 'required',
            'title' => 'required',
            'location' => 'required',
            'price' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        $updated = Trip::where('id', $id)->update($request->all());
        if($updated){
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'fail']);
    }

    public function destroy($id)
    {
        if(Trip::destroy($id)){
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'fail']);
    }

}
