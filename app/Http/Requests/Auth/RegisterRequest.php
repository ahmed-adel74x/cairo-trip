<?php

namespace App\Http\Requests\Auth;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|min:2|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
            'phone'                 => 'nullable|string|min:11|max:15',
        ];
    }

    protected function failedValidation(Validator $validator): void
{
    throw new HttpResponseException(
        $this->errorResponse(
            'validation_error',
            422,
            $this->formatValidationErrors($validator)
        )
    );
}
}