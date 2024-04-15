<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CompteRequest extends FormRequest
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
            'email' => 'required| email:rfc,dns',
            'date_naissance' => 'required| date_format:Y-m-d',
            'sexe' => 'required|boolean',
            'telephone' => 'required|numeric|min:9|max:12',
            'profession' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'nationalite' => 'nullable|required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'num_cni' => 'nullable|numeric',
            'cni_recto' => 'nullable',
            'cni_verso' => Rule::requiredIf( function ($var) {
                $var->cni_recto != null;
            })
        ];
    }

    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            $validator->errors()
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

            'email.required' => 'Veuillez renseignez votre adresse e-mail',
            'email.email' => 'Le format de votre adresse e-mail est incorrect',
            'email.max' => 'Vous avez excédé le nombre de caractères limite pour l\'adresse e-mail',

            'sexe.required' => 'Veuillez renseigner votre genre',
            'sexe.boolean' => 'Valeur du champ sexe non prise en charge',

            'telephone.required' => 'Veuillez renseignez vore numéro de téléphone',
            'telephone.numeric' => 'Le numéro de téléphone de prend en compte que des valeurs numériques',
            'telephone.min' => 'Le numéro de téléphone doit contenir entre neuf (9) et douze (12) chiffres',
            'telephone.max' => 'Le numéro de téléphone doit contenir entre neuf (9) et douze (12) chiffres',

            'profession.regex' => 'Caractère non autorisé pour la profession',

            'nationalite.regex' => 'Caractère non autorisé pour la nationalité',

            'num_cni.numeric' => 'Le numéro de la votre carte d\'identité ne doit contenir que des chiffres',
            
        ];
    }
}
