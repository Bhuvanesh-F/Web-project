<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * RegisterRequest
 *
 * Validates patient/pet owner registration data.
 * Enforces strong password policy and unique email constraint.
 */
class RegisterRequest extends FormRequest
{
    /**
     * Public registration is open to all.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for registration.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->mixedCase(),
            ],
            'role'  => ['sometimes', 'string', Rule::in(['patient', 'pet_owner'])],
            'phone' => ['nullable', 'string', 'regex:/^\+?[\d\s\-]{7,15}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'      => 'Full name is required.',
            'name.min'           => 'Name must be at least 2 characters.',
            'email.required'     => 'Email address is required.',
            'email.unique'       => 'This email address is already registered.',
            'email.email'        => 'Please provide a valid email address.',
            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'phone.regex'        => 'Please enter a valid phone number.',
        ];
    }

    /**
     * @param  Validator  $validator
     * @return never
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
