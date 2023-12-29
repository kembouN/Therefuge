<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateAccountRequest extends FormRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'prenom' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'date_naissance' => 'required|date_format:Y-m-d',
            'lieu_naissance' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'sexe' => 'required|regex:^[0-1]$',
            'telephone' => 'required|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/',
            'lieu_residence' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'profession' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'piece_justificative' => 'nullable',
            'proprietaire' => 'required|regex:^[0-1]$',
            'nationalite' => 'nullable|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/',
            'cni_recto' => 'nullable',
            'cni_verso'=> 'nullable',
            'num_cni' => 'nullable|regex:^[0-9]{10,100}',
        ];
        
    }


    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'message' => 'Erreur de validation', $validator->errors()
        ]));
        
    }

    public function messages(){
        return [
            'nom.required' => 'saisi le nom',
            'nom.regex' => 'seul les carractère(s) a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ sont autorisé(s)',

            'prenom.regex' => 'seul les carractère(s) a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ sont autorisé(s)',

            'date_naissance.required' => 'La date de naissance est requise',
            'date_naissance.regex' => 'La date respecte le format Y-m-d',

            'lieu_naissance.regex' => 'Caractère(s) non autorisé(s)',

            'sexe.required' => 'saisi le sexe',
            'sexe.regex' => 'Le sexe est un soit 0 pour femme, soit 1 pour homme',

            'telephone.required' => 'Le numéro de téléphone est requis',
            'telephone.regex' => 'Le numéro doit commencer par \'+\' suivi du code du pays',
            
            'password.max' => 'Vous ne pouvez exéder le maximum de vingt(20) caractères',

            'lieu_residence.regex' => 'Caractère(s) non autorisé(s)',

            'profession.required' => 'Quelle est votre profession?',
            'profession.regex' => 'Caractère(s) non autorisé(s)',

            'propriétaire.required' => 'Êtes-vous à votre propre compte ou intermédiaire?',

            'num_cni.regex' => 'Seuls les chiffres sont autorisés'
        ];
    }
}
