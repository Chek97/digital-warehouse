<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UpdateUserRequest extends FormRequest
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
        $username = $this->route('id');

        return [
            'name' => 'required',
            'last_name' => 'string|nullable',
            'username' => 'required|max:50|unique:users,username,' . $username,
            'photo' => 'image|mimes:jpeg,jpg,png|max:1024',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new Response($validator->errors(), 422);

        throw new ValidationException($validator, $response);
    }
}
