<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReplyRequest extends FormRequest
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
            'text'       => 'required|string',
            'with_reply' => 'nullable|boolean',
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'text.required'  => 'Поле text обязательно.',
            'with_reply.boolean'  => 'Поле with_reply должно быть 1 или 0.',
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
