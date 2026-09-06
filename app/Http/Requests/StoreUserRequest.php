<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
        'name'                  => 'required|string|max:150',
        'username'              => 'required|unique:users,username|max:50',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|min:6|confirmed',
        'role'                  => 'required|exists:roles,name',
        'unit_id'                  => 'nullable|exists:unit,id'
        ];
    }
}