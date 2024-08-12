<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class PostUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'username' => 'required|max:50|unique:users,username',
            'password' => 'required',
            'role_id' => 'required|exists:roles,id'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new Response($validator->errors(), 422);

        throw new ValidationException($validator, $response);
    }

    // todo: crear mensajes personalizados
}
