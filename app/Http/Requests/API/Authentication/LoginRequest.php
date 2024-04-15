<?php

namespace App\Http\Requests\API\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' =>'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|min:8|max:20',
        ];
    }

    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'statut' => false,
            'message' => 'Erreur de validation: '.$validator->errors()
        ], 400));
    }

    public function messages(){
        return [
            'email.required' => 'L\'adresse e-mail est requise',
            'email.email' => 'Votre adresse n\'est pas conforme',

            'password.required' => 'Le mot de passe est requis',
            'password.regex' => 'Le mot de passe  contient des caractères non autorisés',
            'password.min' => 'Le mot de passe doit contenir entre huit(8) et vingt(20) caractères',
            'password.max' => 'Le mot de passe doit contenir entre huit(8) et vingt(20) caractères',
        ];
    }
}
