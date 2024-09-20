<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reviews;
use App\Models\User;
use App\Models\Appointments;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class AppointmentsController extends Controller
{
    public function store(Request $request)
    {
        //this controller is to store booking details post from mobile app
        $appointment = new Appointments();
        $appointment->user_id = Auth::user()->id;
        $appointment->doc_id = $request->get('doctor_id');
        $appointment->date = $request->get('date');
        $appointment->day = $request->get('day');
        $appointment->time = $request->get('time');
        $appointment->status = 'upcoming'; //new appointment will be saved as 'upcoming' by default
        $appointment->save();


        //if successfully, return status code 200
        return response()->json([
            'success'=>'New Appointment has been made successfully!',
        ], 200);


    }

    public function index()
    {
        //retrieve all appointments from the user
        $appointment = Appointments::where('user_id', Auth::user()->id)->get();
        $doctor = User::where('type', 'doctor')->get();


        //sorting appointment and doctor details
        //and get all related appointment
        foreach($appointment as $data){
            foreach($doctor as $info){
                $details = $info->doctor;//it returns the doctor using that doctor function inside user model which return the doctor with ‘doc_id’=user’s id.
 
                if($data['doc_id'] == $info['id']){
                    $data['doctor_name'] = $info['name'];
                    $data['doctor_profile'] = $info['profile_photo_url']; //typo error found
                    $data['category'] = $details['category'];
                }
            }
        }


        return $appointment;
    }



}
