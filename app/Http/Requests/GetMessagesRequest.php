<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetMessagesRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Поле page должно быть целым числом.',
            'page.min' => 'Поле page должно быть не меньше 1.',
            'per_page.integer' => 'Поле per_page должно быть целым числом.',
            'per_page.min' => 'Поле per_page должно быть не меньше 1.',
            'per_page.max' => 'Поле per_page должно быть не больше 1000.',
        ];
    }

    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
