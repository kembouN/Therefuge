<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LogementGerantFilterRequest extends FormRequest
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
            'id_gerant' => 'required|numeric',
            'type_logement' => 'nullable|numeric',
            'ville' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$',
            'valeur' => 'nulllable|numeric'
        ];
    }

    public function failedValidation(Validator $validator){
        return new HttpResponseException(response()->json([
            $validator->errors()
        ], 400));
    }

    public function messages(){
        return [
            'id_gerant.required' => 'L\'identfiant de l\'utilisateur doit être renseigné',
            'id_gerant.numeric' => 'L\'identifiant de l\'utilisateur est un chiffre',

            'type_logement.numeric' => 'Le type de logement doit être un chiffre',
            
            'ville.regex' => 'La ville contient des caractères non autorisés',

            'valeur.numeric' => 'La valeur entrée doit être un chiffre'
        ];
    }
}
