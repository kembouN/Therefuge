<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class AjouterReactionRequest extends FormRequest
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
            'id_logement' => 'required|numeric',
            'id_compte' => 'required|numeric',
            'commentaire' => 'required|regex:/^[a-zA-Z0-9,!?:._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'avis' => 'nullable|numeric',
            'demande' => 'nullable|boolean',
        ];
    }

    public function failedValidation(Validator $validator){
        Log::error("Ereur de validation");
        throw new HttpResponseException(response()->json([
            'statut' => false,
            'message' => 'Erreur de validation '.$validator->errors()
        ], 400));
    }

    public function messages(){
        return [
            'id_logement.required' => 'L\'identifiant du logement est requis',
            'id_logement.numeric' => 'L\'identifiant du logement doit être un chiffre',

            'id_compte.required' => 'L\'identifiant de l\'utilisateur est requis',
            'id_compte.numeric' => 'L\'identifiant de l\'utilisateur doit être un chiffre',

            'commentaire.required' => 'Veuillez ajouter un commentaire',
            'commentaire.regex' => 'Le commentaire contient des caractères non autorisés',
            'commmentaire.max' => 'Vous avez dépassé le nombre de carctères pour le commentaire',

            'avis.numeric' => 'L\'avis doit être un chiffre',

            'demande.boolean' => 'La valeur de la demande doit être booléenne'
        ];
    }
}
