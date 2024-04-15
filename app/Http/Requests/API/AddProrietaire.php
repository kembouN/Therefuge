<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class AddProrietaire extends FormRequest
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
            'id_logement' => 'required|numeric',
            'nom' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'prenom' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'telephone' => 'required|numeric',
            'email' => 'required|email:rfc, dns',
            'date_naissance' => 'nullable|date_format:Y-m-d',
            'sexe' => 'required|numeric',
            'profession' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/'
        ];
    }

    public function failedValidation(Validator $validator){
        Log::error("Erreur de validation");
        throw new HttpResponseException(response()->json([
            $validator->errors()
        ], 400));
    }

    public function messages(){
        return [
            'id_gerant.required' => 'L\'identifiant de l\'utilisateur est requis',
            'id_gerant.numeric' => 'L\'identifiant de l\'utilisateur doit être un chiffre',

            'id_logement.required' => 'L\'identifiant du logement est requis',
            'id_logement.numeric' => 'L\'identifiant du logement est un chiffre',

            'nom.required' => 'Veuillez renseignez le nom du propriétaire',
            'nom.regex' => 'Le nom du propriétaire contient des caractères non autorisés',

            'prenom.regex' => 'Le prénom contient des caractères non autorisés',

            'telephone.required' => 'Veuillez renseigner le numéro de téléphone',
            'telephone.numeric' => 'Le numéro de téléphone ne contient que des chiffres',

            'email.required' => 'Veuillez renseigner l\'email du propriétaire',
            'email.email' => 'Le format de l\'email est incorrect',

            'date_naissance.date_format' => 'Le format de la date de naissance  est incorrect',

            'sexe.required' => 'Veuillez renseigner le sexe',
            'sexe.numeric' => 'Le sexe doit être un chiffre',

            'profession.regex' => 'La profession contient des caractères non autorisés',
        ];
    }
}
