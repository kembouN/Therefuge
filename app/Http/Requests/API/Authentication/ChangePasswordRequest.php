<?php

namespace App\Http\Requests\API\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
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
            'id_compte' => 'required|numeric',
            'ancien_passe' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:20|min:8',
            'nouveau_passe' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:20|min:8',
            'passe_confirmation' => 'required|same:nouveau_passe',
        ];
    }

    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'statut' => false,
            'message' => $validator->errors()
        ], 400));
    }

    public function messages(){
        return [
            'id_compte.required' => 'L\'identifiant du compte est requis',
            'id_compte.numeric' => 'L\'identifiant est une valeur numérique',

            'ancien_passe.required' => 'Renseigner votre mot de passe actuel',
            'ancien_passe.regex' => 'Le mot de passe actuel contient des caractères non autorisés',
            'ancien_passe.min' => 'Le mot de passe doit contenir entre huit (8) et vingt (20) caractères',
            'ancien_passe.max' => 'Le mot de passe doit contenir entre huit (8) et vingt (20) caractères',

            'nouveau_passe.required' => 'Renseigner votre mot de passe actuel',
            'nouveau_passe.confirmed' => 'Une confirmation du nouveau mot de passe est requise',
            'nouveau_passe.regex' => 'Le mot de passe actuel contient des caractères non autorisés',
            'nouveau_passe.min' => 'Le mot de passe doit contenir entre huit (8) et vingt (20) caractères',
            'nouveau_passe.max' => 'Le mot de passe doit contenir entre huit (8) et vingt (20) caractères',

            'passe_confirmation.required' => 'Veuillez confirmer le nouveau mot de passe',
            'passe_confirmation.same' => 'La confirmation doit être identique au nouveau mot de passe'
        ];
    }
}
