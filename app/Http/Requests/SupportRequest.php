<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SupportRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'nullable|string|max:100',
            'email'   => 'required|email',
            'phone'   => 'required|string|min:11|max:15',
            'problem' => 'required|string|min:10',
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