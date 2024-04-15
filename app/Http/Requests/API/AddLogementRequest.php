<?php

namespace App\Http\Requests\API;

use App\Models\API\Logement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AddLogementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     $logement = Logement::find($this->route('logement'));
        
    //     return $logement && $this->user()->can('create', $logement);
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_gerant' => [
                'required',
                'regex:/^[0-9]$/',
            ],
            'type_logement' => 'required|regex:/^[0-9]$/',
            'description' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\',\- ]+$/|max:255',
            'largeur' => 'required|numeric',
            'longueur' => 'required|numeric',
            'valeur' => 'required|numeric',
            'pays' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'ville' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'latitude' => 'nullable|numeric',
            'longitude' => [
                'nullable',
                // 'required_if:latitude, ',
                // Rule::requiredIf(function($var){
                //     return !empty($var->latitude);
                // }),
                'numeric'
            ],
            'quartier' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'rue' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/'
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
            'id_gerant.regex' => 'L\'identifiant de l\'utilisateur est une valeur numérique',

            'type_logement.required' => 'Veuillez renseigner le type de logement à créer',
            'type_logement.regex' => 'Le type de logement est une valeur chiffre',

            'description.required' => 'Entrez une description pour le logement',
            'description.regex' => 'Caractère(s) non autorisé(s) dans la description',
            'descripion.max' => 'Vous avez excédé le nombre maximal de caractères',

            'largeur.required' => 'Veuillez renseigner les dimensions du logement',
            'largeur.numeric' => 'Les dimensions du logement sont des nombres',

            'longueur.required' => 'Veuillez renseigner les dimensions du logement',
            'longueur.numeric' => 'Les dimensions du logement sont des nombres',

            'valeur.required' => 'Renseignez un valeur pour votre logement',
            'valeur.numeric' => 'Seuls sont pris en charge des nombres pour la valeur',

            'ville.required' => 'Renseignez la ville où est situé le logement',
            'ville.regex' => 'La ville contient des caractères non autorisés',

            'pays.regex' => 'Des caractères non autorisés sont contenus dans le champ pays',

            'latitude.numeric' => 'La latitude doit être un chiffre',
            
            'longitude.numeric' => 'La longitude doit être une chiffre',
            'logitude.required_if' => 'La longitude doit être renseigner avec la latitude',

            'quartier.required' => 'Renseignez dans quel quartier est situé le logement',
            'quartier.regex' => 'Le quartier contient des caractères non autorisés',

            'rue.regex' => 'La rue contient des caractères non autorisés'
        ];
    }
}
