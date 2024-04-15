<?php

namespace App\Http\Requests\API\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
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
            'email' => 'required|email:rfc, dns',
            'question' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
            'reponse' => 'required|regex:/^[a-zA-Z0-9._éèêÉÈÊàôÀÔïÏ\'\- ]+$/|max:255',
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
            'email.required' => 'Veuillez renseignez votre adresse e-mail',
            'email.email' => 'L\'adresse e-mail ne respecte pas le format conventionnel',

            'question.required' => 'Renseignez la question pour réinitialiser votre mot de passe',
            'question.regex' => 'La question contient des caractères non autorosés',
            'question.max' => 'Vous avez excédé le nombre de caractères pour la question',

            'reponse.required' => 'Veuillez renseigner la réponse',
            'reponse.regex' => 'Des caractères non autorisés dans la réponse',
            'reponse.max' => 'Vous avez excédé le nombre de caractère pour la réponse'
        ];
    }
}
