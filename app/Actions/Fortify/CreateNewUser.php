<?php

namespace App\Actions\Fortify;

use App\Models\Doctor;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use App\Rules\ValidUserType;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'type' => ['required', new ValidUserType()],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'type' => $input['type'],
        ]);

        // This part for alternative of the below part
        if($input['type']=='doctor'){
            $doctorInfo = Doctor::create([
                'doc_id' => $user->id,
                'status' => 'active'
            ]);
        }else if($input['type']=='user'){
            $userInfo = UserDetails::create([
                'user_id' => $user->id,
                'status' => 'active'
            ]);


        }
        

        return $user;
    }
}
