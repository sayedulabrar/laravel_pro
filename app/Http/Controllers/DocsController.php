<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reviews;
use App\Models\User;
use App\Models\Appointments;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class DocsController extends Controller
{
    
    public function index()
    {
        //get doctor's appointment, patients and display on dashboard
        $doctor = Auth::user();
        $appointments = Appointments::where('doc_id', $doctor->id)->where('status', 'upcoming')->get();
        //return all data to dashboard
        return response()->json(['doctor'=>$doctor, 'appointments'=>$appointments], 200);
    }

    public function store(Request $request)
    {
        //this controller is to store booking details post from mobile app
        $reviews = new Reviews();
        //this is to update the appointment status from "upcoming" to "complete"
        $appointment = Appointments::where('id', $request->get('appointment_id'))->first();


        //save the ratings and reviews from user
        $reviews->user_id = Auth::user()->id;
        $reviews->doc_id = $request->get('doctor_id');
        $reviews->ratings = $request->get('ratings');
        $reviews->reviews = $request->get('reviews');
        $reviews->reviewed_by = Auth::user()->name;
        $reviews->status = 'active';
        $reviews->save();


        //change appointment status
        $appointment->status = 'complete';
        $appointment->save();


        return response()->json([
            'success'=>'The appointment has been completed and reviewed successfully!',
        ], 200);
    }

}
