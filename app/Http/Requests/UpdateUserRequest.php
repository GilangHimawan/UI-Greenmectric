<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
    $user = request()->route('user');

    return [
        'name'     => 'required|string|max:150',
        'email'    => 'required|email|unique:users,email,' . $user->id,
        'username' => 'required|unique:users,username,' . $user->id,
        'password' => 'nullable|min:6|confirmed',
        'role'     => 'required|array',
        'role.*'   => 'exists:roles,name',
        'unit_id'     => 'nullable|exists:unit,id',
    ];
}
}