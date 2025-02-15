<?php

namespace App\Http\Requests\Auth;

use App\Enums\ROLE;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules()
    {
        return [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
            'role' => ['required', new Enum(ROLE::class), 'not_in:'.ROLE::ADMIN->value],
        ];
    }
}
