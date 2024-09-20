<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'bio_data' => ['nullable', 'string'],
            'category' => ['nullable', 'string', Rule::requiredIf($user->type === 'doctor')],
            'experience' => ['nullable', 'integer', Rule::requiredIf($user->type === 'doctor')],
            'status' => ['nullable', 'string', Rule::requiredIf($user->type === 'user')],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();

            // Update user-specific information
            if ($user->type === 'doctor') {
                $user->doctor()->updateOrCreate(
                    ['doc_id' => $user->id],
                    [
                        'bio_data' => $input['bio_data'],
                        'category' => $input['category'],
                        'experience' => $input['experience'],
                    ]
                );
            } elseif ($user->type === 'user') {
                $user->user_details()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'bio_data' => $input['bio_data'],
                        'status' => $input['status'],
                    ]
                );
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        // Update user-specific information
        if ($user->type === 'doctor') {
            $user->doctor()->updateOrCreate(
                ['doc_id' => $user->id],
                [
                    'bio_data' => $input['bio_data'],
                    'category' => $input['category'],
                    'experience' => $input['experience'],
                ]
            );
        } elseif ($user->type === 'user') {
            $user->user_details()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio_data' => $input['bio_data'],
                    'status' => $input['status'],
                ]
            );
        }
    }
}