<?php

namespace App\Http\Controllers;

use App\Rules\ValidUserType;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{

    public function index()
    {
        $user = array(); //this will return a set of user and doctor data
        $user = Auth::user();
        $doctor = User::where('type', 'doctor')->get();
        $details = $user->user_details;
        $doctorData = Doctor::all();
        //this is the date format without leading
        $date = now()->format('n/j/Y'); //change date format to suit the format in database


        //make this appointment filter only status is "upcoming"
        $appointment = Appointments::where('status', 'upcoming')->where('date', $date)->first();


        //collect user data and all doctor details
        foreach($doctorData as $data){
            //sorting doctor name and doctor details
            foreach($doctor as $info){
                if($data['doc_id'] == $info['id']){
                    $data['doctor_name'] = $info['name'];
                    $data['doctor_profile'] = $info['profile_photo_url'];
                    if(isset($appointment) && $appointment['doc_id'] == $info['id']){
                        $data['appointments'] = $appointment;
                    }
                }
            }
        }


        $user['doctor'] = $doctorData;
        $user['details'] = $details; //return user details here together with doctor list


        return $user; //return all data
    }

    public function login(Request $reqeust)
    {
        //validate incoming inputs
        $reqeust->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);


        //check matching user
        $user = User::where('email', $reqeust->email)->first();


        //check password
        if(!$user || ! Hash::check($reqeust->password, $user->password)){
            throw ValidationException::withMessages([
                'email'=>['The provided credentials are incorrect'],
            ]);
        }


        //then return generated token
        return $user->createToken($reqeust->email)->plainTextToken;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'type' => ['required', new ValidUserType()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
        ]);

        if ($request->type == 'doctor') {
            Doctor::create([
                'doc_id' => $user->id,
                'status' => 'active'
            ]);
        } elseif ($request->type == 'user') {
            UserDetails::create([
                'user_id' => $user->id,
                'status' => 'active'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }





    public function storeFavDoc(Request $request)
    {


        $saveFav = UserDetails::where('user_id',Auth::user()->id)->first();


        $docList = json_encode($request->get('favList'));


        //update fav list into database
        $saveFav->fav = $docList;  //and remember update this as well
        $saveFav->save();


        return response()->json([
            'success'=>'The Favorite List is updated',
        ], 200);
    }

        /**
     * logout.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(){
        $user = Auth::user();
        $user->currentAccessToken()->delete();


        return response()->json([
            'success'=>'Logout successfully!',
        ], 200);
    }

}
