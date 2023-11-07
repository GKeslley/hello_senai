<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        $user = Auth::guard('sanctum')->user()->tokenCan('adm-store');
        $emailRules = [
            'required',
            'email',
            'unique:usuario',
            'max:255'
        ];
        
        if (!$user) $emailRules[] = 'regex:/ba.estudante.senai\.br/';

        return [
           'nome' => 'required|min:5|max:45',
           'email' => $emailRules,
           'senha' => 'required|min:6|max:255'
        ];
    }
}
