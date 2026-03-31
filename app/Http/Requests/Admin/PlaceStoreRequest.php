<?php

namespace App\Http\Requests\Admin;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PlaceStoreRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar'          => 'required|string|max:255',
            'name_en'          => 'required|string|max:255',
            'description_ar'   => 'required|string',
            'description_en'   => 'required|string',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url'        => 'nullable|string',
            'is_free'          => 'required|boolean',
            'price_ar'         => 'required|string',
            'price_en'         => 'required|string',
            'price_number'     => 'required|numeric|min:0',
            'working_hours_ar' => 'required|string',
            'working_hours_en' => 'required|string',
            'location_ar'      => 'required|string',
            'location_en'      => 'required|string',
            'activities_ar'    => 'required|array|min:1',
            'activities_ar.*'  => 'required|string',
            'activities_en'    => 'required|array|min:1',
            'activities_en.*'  => 'required|string',
            'is_active'        => 'nullable|boolean',
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