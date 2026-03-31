<?php

namespace App\Http\Requests\Auth;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $this->formatValidationErrors(
            $validator->errors()->toArray()
        );

        throw new HttpResponseException(
            $this->errorResponse('validation_error', 422, $errors)
        );
    }
}