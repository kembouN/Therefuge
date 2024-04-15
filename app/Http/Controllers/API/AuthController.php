<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Authentication\ChangePasswordRequest;
use App\Http\Requests\API\Authentication\LoginRequest;
use App\Http\Requests\API\Authentication\RegisterRequest;
use App\Http\Requests\API\Authentication\ResetPasswordRequest;
use App\Mail\API\SendVerificationCode;
use App\Models\API\Compte;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Nette\Utils\Random;

class AuthController extends Controller
{
    private $compte;

    public function __construct(){
        $this->compte = new Compte();
    }

    /**
    * @OA\Post(
    *      path="/users/create_account",
    *      operationId="createUserAccount",
    *      tags={"Users"},
    *      summary="Création d'un compte utilisateur",
    *      description="Créer un compte utilisateur (gérant ou client) en remplisant les champs requis à cet effet",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"nom", "prenom", "email", "date_naissance", "sexe", "telephone", "num_cni"},
    *                       @OA\Property(
    *                         property="nom",
    *                         type="string",
    *                         description="Nom de l'utilisateur qui souhaite créer un compte"
    *                       ),
    *                       @OA\Property(
    *                         property="prenom",
    *                         type="string",
    *                         description="Prenom de l'utilisateur"
    *                       ),
    *                       @OA\Property(
    *                         property="email",
    *                         type="string",
    *                         description="Adresse mail de l'utilisateur"
    *                       ),
    *                       @OA\Property(
    *                         property="date_naissance",
    *                         type="string",
    *                         description="Date de naissance de l'utilisateur"
    *                       ),
    *                       @OA\Property(
    *                         property="sexe",
    *                         type="string",
    *                         description="Le sexe de l'utilisateur à valeur booléenne(1 pour la femme, 0 pour l'homme)"
    *                       ),
    *                       @OA\Property(
    *                         property="telephone",
    *                         type="string",
    *                         description="Le numéro de téléphone, ne prend en compte que des chiffres et entre 9 et 12 caractères"
    *                       ),
    *                       @OA\Property(
    *                         property="profession",
    *                         type="string",
    *                         description="La profession est et uniquement prise en compte lorsqu'il s'agit d'un compte gérant et est une valeur booléenne (1 si l'utilisateur est agent immobilier, 0 sinon)"
    *                       ),
    *                       @OA\Property(
    *                         property="nationalite",
    *                         type="string",
    *                         description="La nationalité est optionnelle pour cette version de l'api"
    *                       ),
    *                       @OA\Property(
    *                         property="num_cni",
    *                         type="string",
    *                         description="Le numéro de la carte d'identité est obligatoire pour tout compte"
    *                       ),
    *                       @OA\Property(
    *                         property="password",
    *                         type="string",
    *                         description="Le mot de passe de l'utilisateur"
    *                       ),
    *                       @OA\Property(
    *                         property="id_type_compte",
    *                         type="string",
    *                         description="L'identifiant du type de compte à créer"
    *                       ),
    *                       @OA\Property(
    *                         property="question",
    *                         type="string",
    *                         description="L'utilisateur renseigne une question qui aidera à réinitialiser son mot de passe en cas de perte du précédant"
    *                       ),
    *                       @OA\Property(
    *                         property="reponse",
    *                         type="string",
    *                         description="L'utilisateur renseigne une réponse à la question qu'il a lui même renseigné"
    *                       ),
    *          ),
    *      ),
    *      @OA\Response(
    *          response=201,
    *          description="Création du compte réussie, vérifiez votre adresse mail",
    *       ),
    *       @OA\Response(
    *         response=400,
    *         description="Bad request",
    *       ),
    *       @OA\Response(
    *         response=401,
    *         description="Unauthenticated",
    *       ),
    *       @OA\Response(
    *         response=403,
    *         description="Forbidden",
    *       ),
    *       @OA\Response(
    *         response=404,
    *         description="Not found",
    *       ),
    *       @OA\Response(
    *         response=422,
    *         description="Unprocessable",
    *       ),
    *     )
    */
    public function createAccount(RegisterRequest $formulaire){
        DB::beginTransaction();
        try {
            $userData = [
                'email' => $formulaire->email,
                'password' => Hash::make($formulaire->password),
                'id_type_compte' => $formulaire->id_type_compte,
                'reset_question' => $formulaire->question,
                'reset_response' => $formulaire->reponse,
                'activated_account' => $this::COMPTE_INACTIF,
                'verification_code' => Random::generate(6, '0-9')
            ];

            $user = User::create($userData);
            if(!$user){
                $this->message = "Erreur lors de la création de votre compte";
                throw new Exception($this->message, 422);
            }

            $accountData = [
                'nom' => $formulaire->nom,
                'prenom' => $formulaire->prenom,
                'date_naissance' => $formulaire->date_naissance,
                'sexe' => $formulaire->sexe,
                'telephone' => $formulaire->telephone,
                'email' => $formulaire->email,
                'profession' => $formulaire->profession,
                'nationalite' => $formulaire->nationalite,
                'num_cni' => $formulaire->num_cni,
                'id_user' => $user->id,
                'id_type_compte' => $formulaire->id_type_compte
            ];
    
            $createCompte = $this->compte->creerCompte($accountData);
    
            if(!$createCompte){
                $this->message = "Une erreur s'est produite lors de la création de votre compte";
                throw new Exception($this->message, 422);
            }

            $mailData = [
                'id_compte' => $createCompte->id,
                'nom' => $createCompte->nom,
                'prenom' => $createCompte->prenom,
                'date_naissance' => $createCompte->date_naissance,
                'sexe' => $createCompte->sexe,
                'telephone' => $createCompte->telephone,
                'email' => $createCompte->email,
                'profession' => $createCompte->profession,
                'nationalite' => $createCompte->nationalite,
                'num_cni' => $createCompte->num_cni,
                'id_user' => $createCompte->id_user,
                'id_type_compte' => $user->id_type_compte,
                'code_verifcation' => $user->verification_code,
                'subject' => 'verification'
            ];

            if(!Mail::to($user->email)->send(new SendVerificationCode($mailData))){
                $this->message = "Une erreur est survenue lors de l'envoi du mail de vérification de compte";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Utilisateur n° $user->id et compte n°$createCompte->id créés avec succès");
            return $this->succes(null, "Un mail de  vérification de vos coordonées à été envoyé, identifiez-vous", 201);

        } catch (Exception $th) {
            DB::rollBack();
            if($th->getCode() == 422){
                Log::error($this->message.'Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }

    /**
    * @OA\Get(
    *      path="/user/{user_id}/check_email/{codeverification}",
    *      operationId="VerifyEmail",
    *      tags={"Users"},
    *      summary="Vérification de l'adresse e-mail",
    *      description="L'utilisateur reçoit un code unique qu'il renseigne lui permettant de vérifier son adresse e-mail",
    *      @OA\Parameter(
    *          description="Identifiant unique de l'utilisateur",
    *          in="path",
    *          name="user_id",
    *          required=true,
    *          @OA\Schema(type="string"),
    *      ),
    *      @OA\Parameter(
    *        description="Le code unique de vérification envoyé à l'utilisateur via un e-mail",
    *        name="codeverification",
    *        in="path",
    *        required=true,
    *        @OA\Schema(type="string")
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description=",Vérification de votre adresse mail effectuée",
    *       ),
    *       @OA\Response(
    *         response=401,
    *         description="Unauthenticated",
    *       ),
    *       @OA\Response(
    *         response=403,
    *         description="Forbidden",
    *       ),
    *       @OA\Response(
    *         response=404,
    *         description="Not found",
    *       ),
    *       @OA\Response(
    *         response=422,
    *         description="Unprocessable",
    *       ),
    *     )
    */
    
    public function verifierEmail($idUser, $codeverification){
        DB::beginTransaction();
        try {
            $user = User::find($idUser);
            if(empty($user)){
                $this->message = "L'identifiant ne correspond à aucun utilisateur";
                throw new Exception($this->message, 404);
            }

            $datecreation = Carbon::parse($user->created_at)->timestamp;
            $dateActuelle = Carbon::now()->timestamp;
            $datelimite = $datecreation + 3600;
            if($dateActuelle > $datelimite){
                $this->message = "Votre code de vérification n'est plus valide, demandez-en un nouveau";
                throw new Exception($this->message, 422);
            }else if($dateActuelle <= $datelimite && $user->verification_code === $codeverification){
                $dataActivation = [
                    'activation_date' => date('Y-m-d H:i:s', $dateActuelle),
                    'activated_account' => $user->id_type_compte == $this::COMPTE_GERANT ? $this::COMPTE_INACTIF : $this::COMPTE_ACTIF
                ];
                User::where('id', $idUser)->update($dataActivation);
                DB::commit();
                Log::info("Adresse e-mail vérifée");
                return $this->succes(null, "Adresse e-mail vérifiée, vous pouvez vous connecter avec vos identifiants");
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 422 || $th->getCode() == 404){
                Log::error($this->message.' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }

    /**
    * @OA\Get(
    *      path="/user/{user_id}/account-activate",
    *      operationId="accountActivation",
    *      security={{"sanctum":{}}},
    *      tags={"Users"},
    *      summary="Activer un compte utilisateur",
    *      description="Lorsque qu'un compte gérant est créé, il est nécessaire que l'administrateur valide le compte pour qu'il soit actif",
    *      @OA\Parameter(
    *          description="L'identifiant unique de l'utilisateur",
    *          in="path",
    *          name="user_id",
    *          required=true,
    *          @OA\Schema(type="string"),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Compte activé",
    *       ),
    *       @OA\Response(
    *         response=401,
    *         description="Unauthenticated",
    *       ),
    *       @OA\Response(
    *         response=403,
    *         description="Forbidden",
    *       ),
    *       @OA\Response(
    *         response=404,
    *         description="Not found",
    *       ),
    *       @OA\Response(
    *         response=422,
    *         description="Unprocessable",
    *       ),
    *     )
    */

    public function activerCompte($idUser){
        DB::beginTransaction();
        try {
            if(empty(User::find($idUser))){
                $this->message = "Utilisateur introuvable";
                throw new Exception($this->message, 404);
            }

            $dateActivation = Carbon::now()->timestamp;
            $activationData = [
                'activated_account' => $this::COMPTE_ACTIF,
                'activation_date' => date('Y-m-d H:i:s', $dateActivation)
            ];

            $activerCompte = User::where('id', $idUser)->update($activationData);
            if(!$activerCompte){
                $this->message = "Erreur lors de l'activation du compte ";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Le compte n°$idUser est activé");
            return $this->succes(null, "Le compte a été activé avec succès");
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 404 || $th->getCode() == 422){
                Log::error($this->message.' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }


    /**
    * @OA\Post(
    *      path="/login",
    *      operationId="userLogin",
    *      tags={"Users"},
    *      summary="Connexion au compte utilisateur",
    *      description="L'utilisateur renseigne son adaresse e-mail et son mot de passe pour se connecter à son compte",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"email", "password"},
    *               @OA\Property(
    *                 property="email",
    *                 type="string",
    *               ),
    *               @OA\Property(
    *                 property="password",
    *                 type="string",
    *               ),
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Connexion réussie",
    *       ),
    *       @OA\Response(
    *         response=400,
    *         description="Bad request",
    *       ),
    *       @OA\Response(
    *         response=401,
    *         description="Unauthenticated",
    *       ),
    *       @OA\Response(
    *         response=403,
    *         description="Forbidden",
    *       ),
    *       @OA\Response(
    *         response=404,
    *         description="Not found",
    *       ),
    *     )
    */
    public function accountLogin(LoginRequest $request){
        DB::beginTransaction();
        try {

            if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $this->message = "Votre mot de passe ou votre adresse e-mail est incorrect";
                throw new Exception($this->message, 401);
            }

            $user = Auth::user();

            if($user->activated_account == $this::COMPTE_INACTIF){
                $this->message = "Votre compte n'est pas activé, rapprochez vous des administrateurs pour y remédier";
                throw new Exception($this->message, 401);
            }

            $informationCompte = $this->compte->selectionnerCompteParIdUser($user->id);
            if(empty($informationCompte)){
                $this->message = "Aucun compte n'a été retrouvé";
                throw new Exception($this->message, 404);
            }
    
            switch ($informationCompte->id_type_compte) {
                case $this::COMPTE_ADMIN:
                    $compte = "Administrateur";
                    $tokenName = "admin_connected";
                    break;

                case $this::COMPTE_GERANT:
                    $compte = "Gérant";
                    $tokenName = "gerant_connected";
                    break;

                case $this::COMPTE_CLIENT:
                    $compte = "Client";
                    $tokenName = "client_connected";
                    break;
                default:
                    $tokenName = 'user_connected';
                    break;
                
            }
            
            $connected = User::where('email', $request->email)->update(['last_connection' => now()]);
            if(!$connected){
                $this->message = "Erreur survenue";
                throw new Exception($this->message, 422);
            }

            $donne = [
                'id_compte' => $informationCompte->id,
                'id_user' => $informationCompte->id_user,
                'nom_user' => $informationCompte->nom,
                'prenom_user' => $informationCompte->prenom,
                'naissance_user' => $informationCompte->date_naissance,
                'age' => age($informationCompte->date_naissance),
                'sexe' => $informationCompte->sexe == $this::FEMININ ? 'Féminin' : 'Masculin',
                'telephone' => $informationCompte->telephone,
                'email' => $informationCompte->email,
                'prefession' => $informationCompte->profession,
                'user_id' => $informationCompte->id_user,
                'type_compte' => $informationCompte->id_type_compte,
                'libelle_compte' => $compte,
                'full_name' => $informationCompte->nom.' '.$informationCompte->prenom,
                'token' => $user->createToken($tokenName)->plainTextToken
            ];

            DB::commit();
            Log::info("Authentification réussie, user: $user->id");
            return $this->succes($donne, "Bienvenue ".$informationCompte->prenom.'. De nouvelles expériences vous attendent😉🎪');
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 422 || $th->getCode() == 401 || $th->getCode() == 404){
                Log::error($this->message.' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }

    /**
    * @OA\Post(
    *      path="/user/reset-password",
    *      operationId="resetPassword",
    *      tags={"Users"},
    *      summary="Réinitialiser le mot de passe",
    *      description="En cas de perte du mot de passe, l'utilisateur peut rénitialiser et en obtenir un nouveau via un mail. Il suffit de renseigner la question et la réponse de réinitialisation qu'il à fourni lors de la création de compte",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"email", "question", "reponse"},
    *               @OA\Property(
    *                 property="email",
    *                 type="string",
    *                 description="Adresse e-mail de l'utilisateur",
    *               ),
    *               @OA\Property(
    *                 property="question",
    *                 type="string",
    *                 description="Question de réinitialisation du mot de passe",
    *               ),
    *               @OA\Property(
    *                 property="reponse",
    *                 type="string",
    *                 description="Réponse à la question de réinitialisation",
    *               ),
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Mot de passe réinitialisé",
    *       ),
    *       @OA\Response(
    *         response=400,
    *         description="Bad request",
    *       ),
    *       @OA\Response(
    *         response=401,
    *         description="Unauthenticated",
    *       ),
    *       @OA\Response(
    *         response=403,
    *         description="Forbidden",
    *       ),
    *       @OA\Response(
    *         response=404,
    *         description="Not found",
    *       ),
    *       @OA\Response(
    *         response=422,
    *         description="Unprocessable",
    *       ),
    *     )
    */

    public function reinitialiserMotDePasse(ResetPasswordRequest $request){
        DB::beginTransaction();
        try {

            $user = User::where('email', $request->email)->first();
            if(empty($user)){
                $this->message = "L'adresse e-mail ne correspond à aucun compte";
                throw new Exception($this->message, 404);
            }

            if(!(strtolower($request->question) == strtolower($user->reset_question) && strtolower($request->reponse) == strtolower($user->reset_response))){
                $this->message = "Les informations de rappel ne correspondent pas.";
                throw new Exception($this->message, 404);
            }

            $nom = explode('@',$user->email);
            $codeUnique = Random::generate(3);
            $password = '#'.$nom[0].'@'.$codeUnique;
            $updatedUser = User::where('id', $user->id)->update(['password' => $password]);

            if(!$updatedUser){
                $this->message = "Une erreur est survenue";
                throw new Exception($this->message, 422);
            }

            // $compte = Compte::where('id_user', $user->id)->first();
            // if(empty($compte)){
            //     $this->message = "Aucun compte correspondant";
            //     throw new Exception($this->message, 404);
            // }

            $mailData = [
                'passe' => $password,
                'subject' => 'reinitialisation'
            ];

            if(!Mail::to($user->email)->send(new SendVerificationCode($mailData))){
                $this->message = "Une erreur est survenue lors de l'envoi du mail de vérification de compte";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Mot de passe renouvellé pour l'utilisateur n°".$user->id);
            return $this->succes(null, 'Mot de passe renouvellé');
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 422 || $th->getCode() == 404){
            Log::error($this->message.'  Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }

    }

    /**
    * @OA\Post(
    *      path="/user/change-password",
    *      operationId="changePassword",
    *      tags={"Users"},
    *      security={{"sanctum":{}}},
    *      summary="Changer de mot de passe",
    *      description="Si l'utilisateur souhaite changer de mot de passe pour un plus robuste",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"id_compte", "ancien_passe", "nouveau_passe", "passe_confirmation"},
    *               @OA\Property(
    *                 property="id_compte",
    *                 type="string",
    *               ),
    *               @OA\Property(
    *                 property="ancien_passe",
    *                 type="string",
    *               ),
    *               @OA\Property(
    *                 property="nouveau_passe",
    *                 type="string",
    *               ),
    *               @OA\Property(
    *                 property="passe_confirmation",
    *                 type="string",
    *               ),
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Mot de passe renouvellé",
    *       ),
    *       @OA\Response(
    *         response=400,
    *         description="Bad request",
    *       ),
    *       @OA\Response(
    *         response=401,
    *         description="Unauthenticated",
    *       ),
    *       @OA\Response(
    *         response=403,
    *         description="Forbidden",
    *       ),
    *       @OA\Response(
    *         response=404,
    *         description="Not found",
    *       ),
    *       @OA\Response(
    *         response=422,
    *         description="Unprocessable",
    *       ),
    *     )
    */

    public function changerMotDePasse(ChangePasswordRequest $request){
        DB::beginTransaction();
        try {

            $compte = $this->compte->obtenirCompteSpecifique($request->id_compte);
            if(empty($compte)){
                $this->message = "Aucun compte ne correspond à l'identifiant";
                throw new Exception($this->message, 404);
            }

            $user = User::find($compte->id_user);
            if(empty($user)){
                $this->message = "Aucun utilisateur correspondant";
                throw new Exception($this->message, 404);
            }

            $correspondance = Hash::check($request->ancien_passe, $user->password);
            if(!$correspondance){
                $this->message = "Vérifiez votre ancien mot de passe";
                throw new Exception($this->message, 422);
            }

            $updatedPassword = User::where('id', $compte->id_user)->update(['password' => Hash::make($request->nouveau_passe)]);
            if(!$updatedPassword){
                $this->message = "Erreur lors de la mise à jour du mot de passe";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Mise à jour du mot de passe effectuée avec succes");
            return $this->succes(null, "Votre mot de passe a été mise à jour");

        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 422 || $th->getCode() == 404){
            Log::error($this->message.' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }
}
