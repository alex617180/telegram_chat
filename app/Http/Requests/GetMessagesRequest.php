<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetMessagesRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * Кастомные сообщения ошибок.
     *
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
     * Обработка ошибок валидации.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
