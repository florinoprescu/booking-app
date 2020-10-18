<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
/*use Illuminate\Foundation\Auth\ThrottlesLogins;*/

class UsersController extends Controller
{
    /*use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 1;*/
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function generateApiToken() {
        return base64_encode(Str::random(60));
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->input('email'))->first();
        if($user->failed_login_count > 5 && (time() - strtotime($user->failed_login_date)) <= 60) {
            return response()->json(['status' => 'fail'], 429);//Too Many Requests
        }
        elseif($user->failed_login_count > 5) {
            User::where('email', $request->input('email'))->update([
                'failed_login_count' => 1,
                'failed_login_date' => \Carbon\Carbon::now()->toDateTimeString()
            ]);
            return response()->json(['status' => 'fail'], 401);//unauthorized
        }

        if (!empty($user) && Hash::check($request->input('password'), $user->password) && $user) {//check email, password if user with that email exists
            $apikey = $this->generateApiToken();
            User::where('email', $request->input('email'))->update([
                'api_key' => "$apikey",
                'failed_login_count' => 0,
                'failed_login_date' => NULL
            ]);
            return response()->json(['status' => 'success', 'api_key' => $apikey]);
        }
        elseif(!empty($user)) {//user exists but password wrong
            if($user->failed_login_date) {
                User::where('email', $request->input('email'))->update([
                    'failed_login_count' => $user->failed_login_count + 1
                ]);
            }
            else {
                User::where('email', $request->input('email'))->update([
                    'failed_login_count' => $user->failed_login_count + 1,
                    'failed_login_date' => \Carbon\Carbon::now()->toDateTimeString()
                ]);
            }
            return response()->json(['status' => 'fail'], 401);//unauthorized
        }
        else {
            return response()->json(['status' => 'fail'], 401);//unauthorized
        }
    }

    public function register(Request $request){
        if ($request->has('first_name') && $request->has('last_name') && $request->has('email') && $request->has('password')) {
            $this->validate($request, [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:5|max:15',
            ]);

            $user = new User;
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));//no validation added here
            $user->api_key = $this->generateApiToken();
            if($user->save()){
                return "User successfully created!";
            } else {
                return "User was not created!";
            }
        } else {
            return "Please provide First Name, Last Name, Email and Password!";
        }
    }
}
