<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

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
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users',
                'regex:/^.+@(bracu\.ac\.bd|g\.bracu\.ac\.bd)$/i'
            ],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'email.regex' => 'Only BRACU emails (@bracu.ac.bd or @g.bracu.ac.bd) are allowed.',
        ])->validate();

        $email = $input['email'];
        $role = 'student'; // Default

        if (str_ends_with(strtolower($email), '@bracu.ac.bd')) {
            $role = 'faculty';
        } elseif (str_ends_with(strtolower($email), '@g.bracu.ac.bd')) {
            $role = 'student';
        }

        return User::create([
            'name' => $input['name'],
            'email' => $email,
            'password' => Hash::make($input['password']),
            'role' => $role,
        ]);
    }
}
