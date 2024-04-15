<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;


class UpdateCompteRequest extends FormRequest
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
            'date_naissance' => 'required| date_format:Y-m-d',
            'sexe' => 'required|boolean',
            'telephone' => [
                'required',
                'numeric',
            ],
            'email' => [
                'required',
                'email:rfc, dns',
            ],
            'num_cni' => [
                'required',
                'numeric',
            ],
            'cni_recto' => [
                'nullable',
                'image',
                'mimes:jpg,png,jpeg,gif,svg',
                'max:2048',
                'dimensions:max_width=512,max_height=512',
            ],
            'cni_verso' => [
                'nullable',
                'image',
                'mimes:jpg,png,jpeg,gif,svg',
                'max:2048',
                'dimensions:max_width=512,max_height=512',
            ]
        ];
    }

    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'statut' => false,
            'message' => $validator->errors(),
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

            // 'email.required' => 'Veuillez renseignez votre adresse e-mail',
            'email.email' => 'Le format de votre adresse e-mail est incorrect',
            // 'email.max' => 'Vous avez excédé le nombre de caractères limite pour l\'adresse e-mail',
            // 'email.unique' => 'Cet adresse e-mail est déjà utilisée',

            'sexe.required' => 'Veuillez renseigner votre genre',
            'sexe.boolean' => 'Valeur du champ sexe non prise en charge',

            'telephone.required' => 'Veuillez renseignez vore numéro de téléphone',
            'telephone.numeric' => 'Le numéro de téléphone de prend en compte que des valeurs numériques',
            'telephone.min' => 'Le numéro de téléphone doit contenir entre neuf (9) et douze (12) chiffres',
            'telephone.max' => 'Le numéro de téléphone doit contenir entre neuf (9) et douze (12) chiffres',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',

            'num_cni.numeric' => 'Le numéro de la votre carte d\'identité ne doit contenir que des chiffres',
            'num_cni.unique' => 'Ce numéro de carte d\'identité est déjà utilisé',

            'cni_recto.image' => 'La cni est une image',
            'cni_recto.mimes' => 'Le format de l\'image n\'est pas pris en compte',
            'cni_recto.max' => 'La résolution de la cni n\'est pas supportée',
            'cni_recto.dimensions' => 'Les dimensions de l\'image sont trop grandes',

            'cni_verso.image' => 'La cni est une image',
            'cni_verso.mimes' => 'Le format de l\'image n\'est pas pris en compte',
            'cni_verso.max' => 'La résolution de la cni n\'est pas supportée',
            'cni_verso.dimensions' => 'Les dimensions de l\'image sont trop grandes',

        ];
    }
    
}
