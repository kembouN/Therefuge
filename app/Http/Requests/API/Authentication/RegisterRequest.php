<?php

namespace App\Http\Requests\API\Authentication;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class RegisterRequest extends FormRequest
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
            'nom' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'prenom' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('users', 'email')
            ],
            'date_naissance' => 'required|date_format:Y-m-d',
            'sexe' => 'required|boolean',
            'telephone' => [
                'required',
                'numeric',
                'min:9',
                // 'max:12',
                Rule::unique('comptes', 'telephone')
            ],
            'profession' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'nationalite' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'num_cni' => [
                'required',
                'numeric',
                Rule::unique('comptes', 'num_cni')
            ],
            'password' => 'required|confirmed|string|min:8|max:20',
            'id_type_compte' => 'required|numeric',
            'question' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:100',
            'reponse' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255'
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
            'nom.required' => 'Veuillez renseigner votre nom',
            'nom.regex' => 'Le nom contient des caractères spéciaux non pris en charge',
            'nom.max' => 'Vous avez excédé le nombre de caractères limite pour le nom',

            'prenom.required' => 'Veuillez renseigner votre prénom',
            'prenom.regex' => 'Le prénom contient des caractères spéciaux non pris en charge',
            'prenom.max' => 'Vous avez excédé le nombre de caractères limite pour le prénom',

            'date_naissance.required' => 'Veuillez renseignez votre date de naissance',
            'date_naissance.date_format' => 'Votre date de naissance ne correspond pas au format requis',

            'email.required' => 'Veuillez renseignez votre adresse e-mail',
            'email.email' => 'Le format de votre adresse e-mail est incorrect',
            'email.max' => 'Vous avez excédé le nombre de caractères limite pour l\'adresse e-mail',
            'email.unique' => 'Cet adresse e-mail est déjà utilisée',

            'sexe.required' => 'Veuillez renseigner votre genre',
            'sexe.boolean' => 'Valeur du champ sexe non prise en charge',

            'telephone.required' => 'Veuillez renseignez vore numéro de téléphone',
            'telephone.numeric' => 'Le numéro de téléphone de prend en compte que des valeurs numériques',
            'telephone.min' => 'Le numéro de téléphone doit contenir au moins  neuf (9) chiffres',
            'telephone.max' => 'Le numéro de téléphone doit contenir au plus douze (12) chiffres',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',

            'profession.regex' => 'Caractère non autorisé pour la profession',

            'nationalite.regex' => 'Caractère non autorisé pour la nationalité',

            'num_cni.numeric' => 'Le numéro de la votre carte d\'identité ne doit contenir que des chiffres',
            'num_cni.unique' => 'Ce numéro de carte d\'identité est déjà utilisé',
            
            'id_type_compte.required' => 'le type de compte doit être renseigné',
            'id_type_compte.numeric' => 'Le format du type de compte est incorrect',

            'password.required' => 'Veuillez renseignez un nouveau mot de passe pour votre compte',
            'password.confirmed' => 'Le nouveau mot de passe n\'est pas confirmé',
            'password.min' => 'Le mot de passe doit contenir entre huit(8) et vingt(20) caractères',
            'password.max' => 'Le mot de passe doit contenir entre huit(8) et vingt(20) caractères',

            'question.required' => 'Veuillez renseigner une question de réinitialisation de mot de passe',
            'question.regex' => 'La question contient des caractères non autorisés',

            'reponse.required' => 'Veuillez renseigner une réponse à la question de réinitialisation',
            'reponse.regex' => 'La réponse contient des caractères non autorisés'
        ];
    }
}
