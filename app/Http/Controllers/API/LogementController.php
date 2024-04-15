<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AddLogementRequest;
use App\Http\Requests\API\AddProrietaire;
use App\Http\Requests\API\AjouterReactionRequest;
use App\Http\Requests\API\Authentication\UpdateLogementRequest;
use App\Http\Requests\API\LogementGerantFilterRequest;
use App\Models\API\Compte;
use App\Models\API\Contrat;
use App\Models\API\ContratAssurance;
use App\Models\API\Localisation;
use App\Models\API\Logement;
use App\Models\API\Proprietaire;
use App\Models\API\Reaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LogementController extends Controller
{

    const PAYS_DEFAUT = "Cameroun";
    private $logement;
    private $reaction;
    private $contrat;
    private $compte;
    private $localisation;
    private $proprio;

    public function __construct()
    {
        $this->logement = new Logement();
        $this->reaction = new Reaction();
        $this->contrat = new Contrat();
        $this->compte = new Compte();
        $this->proprio = new Proprietaire();
        $this->localisation = new Localisation();
    }


    public function index(){
        //
    }

    
    public function show(){
        //
    }

    /**
    * @OA\Post(
    *      path="/logement",
    *      operationId="addLogement",
    *      tags={"Logements"},
    *      security={{"sanctum":{}}},
    *      summary="Ajouter un nouveau logement",
    *      description="Fonctionnalité permettant au gérant d'ajouter un nouveau logement",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"id_gerant", "type_logement", "description", "largeur", "longueur", "valeur", "ville", "quartier"},
    *               @OA\Property(property="id_gerant", type="string"),
    *               @OA\Property(property="type_logement", type="string"),
    *               @OA\Property(property="description", type="string"),
    *               @OA\Property(property="largeur", type="string"),
    *               @OA\Property(property="longueur", type="string"),
    *               @OA\Property(property="valeur", type="string"),
    *               @OA\Property(property="pays", type="string"),
    *               @OA\Property(property="ville", type="string"),
    *               @OA\Property(property="latitude", type="string"),
    *               @OA\Property(property="longitude", type="string"),
    *               @OA\Property(property="quartier", type="string"),
    *               @OA\Property(property="rue", type="string")
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Logement ajouté",
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

    public function store(AddLogementRequest $addlogementform){
        DB::beginTransaction();
        try {
            $gerant = $this->compte->obtenirCompteSpecifique($addlogementform->id_gerant);
            if(empty($gerant)){
                $this->message = "Aucun compte utilisateur ne correspond aux informations";
                throw new Exception($this->message, 404);
            }

            $user = Auth::user();
            if(empty($user)){
                $this->message = "Aucun utilisateur correspondant";
                throw new Exception($this->message, 404);
            }else if($user->id_type_compte != $this::COMPTE_GERANT){
                $this->message = "Vous n'êtes pas éligible à effectuer cette action";
                throw new Exception($this->message, 422);
            }

            $locationData = [
                'pays' => empty($addlogementform->pays) ? self::PAYS_DEFAUT : $addlogementform->pays,
                'ville' => $addlogementform->ville,
                'latitude' => $addlogementform->latitude,
                'longitude' => $addlogementform->longitude,
                'quartier' => $addlogementform->quartier,
                'rue' => $addlogementform->rue
            ];

            $localisation = $this->localisation->nouvelleLocalisation($locationData);
            if(!$localisation){
                $this->message = "Erreur lors de l'ajout de la localisation";
                throw new Exception($this->message, 422);
            }

            $logementData = [
                'id_gerant' => $addlogementform->id_gerant,
                'code' => generateCode(5),
                'disponibilite_logement' => $this::LOGEMENT_DISPONIBLE,
                'visibilite' => $this::LOGEMENT_INVISIBLE,
                'description' => $addlogementform->description,
                'valeur' => $addlogementform->valeur,
                'largeur' => $addlogementform->largeur,
                'longueur' => $addlogementform->longueur,
                'id_type_logement' => $addlogementform->type_logement,
                'id_localisation' => $localisation->id
            ];

            $logement = $this->logement->ajouterLogement($logementData);
            if(!$logement){
                $this->message = "Erreur lors de l'enregistrement du logement";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Nouveau logement ajouté");
            return $this->succes($logement, "Logement ajouté avec succès, vérifiez et activez la visibilité");
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
    * @OA\Put(
    *      path="/logement/{id_logement}",
    *      operationId="updateLogement",
    *      tags={"Logements"},
    *      security={{"sanctum":{}}},
    *      summary="Mise à jour",
    *      description="Mettre à jour les informations d'un logement spécifique",
    *      @OA\Parameter(
    *          description="Identifiant du logement",
    *          in="path",
    *          name="id_logement",
    *          required=true,
    *          @OA\Schema(type="string"),
    *      ),
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"id_gerant", "type_logement", "description", "largeur", "longueur", "valeur", "ville", "quartier"},
    *               @OA\Property(property="id_gerant", type="string"),
    *               @OA\Property(property="type_logement", type="string"),
    *               @OA\Property(property="description", type="string"),
    *               @OA\Property(property="largeur", type="string"),
    *               @OA\Property(property="longueur", type="string"),
    *               @OA\Property(property="valeur", type="string"),
    *               @OA\Property(property="pays", type="string"),
    *               @OA\Property(property="ville", type="string"),
    *               @OA\Property(property="latitude", type="string"),
    *               @OA\Property(property="longitude", type="string"),
    *               @OA\Property(property="quartier", type="string"),
    *               @OA\Property(property="rue", type="string")
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Logement ajouté",
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

    public function update(AddLogementRequest  $formupdatelogement, $idLogement){

        DB::beginTransaction();
        try {
            $logement = $this->logement->logementSpecifique($idLogement);
            if(empty($logement)){
                $this->message = "Aucun logement ne correspond à l'identifiant entré";
                throw new Exception($this->message, 404);
            }
    
            $user = Auth::user();
            if(empty($user)){
                $this->message = "Aucun compte utilisateur correspondant aux informations";
                throw new Exception($this->message, 404);
            }else if($logement->id_gerant != $formupdatelogement->id_gerant || $user->id_type_compte != $this::COMPTE_GERANT){
                $this->message = "Vous n'êtes pas autorisé à effectuer cette action";
                throw new Exception($this->message, 422);
            }
    
            $locationData = [
                'pays' => $formupdatelogement->pays,
                'ville' => $formupdatelogement->ville,
                'latitude' => $formupdatelogement->latitude,
                'longitude' => $formupdatelogement->longitude,
                'quartier' => $formupdatelogement->quartier,
                'rue' => $formupdatelogement->rue
            ];
    
            $logementData = [
                'id_gerant' => $formupdatelogement->id_gerant,
                'description' => $formupdatelogement->description,
                'valeur' => $formupdatelogement->valeur,
                'largeur' => $formupdatelogement->largeur,
                'longueur' => $formupdatelogement->longueur,
                'id_type_logement' => $formupdatelogement->type_logement,
            ];
    
            $localisation = $this->localisation->modifierLocalisation($logement->id_localisation, $locationData);
            $logement = $this->logement->modifierLogement($idLogement, $logementData);
            if(!$localisation || !$logement){
                $this->message = "Erreur lors de la mise à jour des données";
                throw new Exception($this->message, 422);
            }
    
            DB::commit();
            Log::info("Mise à jour des données du logement effetuée");
            return $this->succes(null, "Mise à jour des données du logement effetuée");
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 404 || $th->getCode() == 422){
                Log::error($this->message.' fichier: '.$th->getFile().' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }

    /**
    * @OA\Get(
    *      path="/logement/{id_logement}/changer-visibilite",
    *      operationId="changeVisibility",
    *      tags={"Logements"},
    *      security={{"sanctum":{}}},
    *      summary="Modifier la visibilité",
    *      description="Permet à l'utilisateur de définir si le logement doit être visible au grand public ou non",
    *      @OA\Parameter(
    *          description="Identifiant du logement",
    *          in="path",
    *          name="id_logement",
    *          required=true,
    *          @OA\Schema(type="string"),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Visibilité changée",
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

    public function visibiliteOrNot($idLogement){
        DB::beginTransaction();
        try {
            $logement = $this->logement->logementSpecifique($idLogement);
            if(empty($logement)){
                $this->message = "Aucun logement correspondant n'a été trouvé";
                throw new Exception($this->message, 404);
            }

            $user = Auth::user();
            if($user->id_type_compte != $this::COMPTE_GERANT){
                $this->message = "Vous n'êtes pas autorisé à affectuer cette action";
                throw new Exception($this->message, 422);
            }
            
            $visibilite = $logement->visibilite == $this::LOGEMENT_INVISIBLE ? $this::LOGEMENT_VISIBLE : $this::LOGEMENT_INVISIBLE;
            $updateVisibilite = $this->logement->modifierLogement($idLogement, ['visibilite' => $visibilite]);
            if(!$updateVisibilite){
                $this->message = "La modification de la visibilité du logement a échoué";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Mise à jour de la visibilité effectuée");
            return $this->succes(null, "Modification de la visibilité effectuée");
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
    * @OA\Post(
    *      path="/logement/ajouter-proprio",
    *      operationId="addProprio",
    *      tags={"Logements"},
    *      security={{"sanctum":{}}},
    *      summary="Ajouter un propiétaire de logement",
    *      description="Dans le cas ou le gérant d'un logement n'est pas le propriétaire, il est nécessaire de renseigner des informations sur le propriétaire",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"id_gerant", "id_logement", "nom", "telephone", "email", "sexe"},
    *               @OA\Property(property="id_gerant", type="string"),
    *               @OA\Property(property="id_logement", type="string"),
    *               @OA\Property(property="nom", type="string"),
    *               @OA\Property(property="prenom", type="string"),
    *               @OA\Property(property="telephone", type="string"),
    *               @OA\Property(property="email", type="string"),
    *               @OA\Property(property="date_naissance", type="string"),
    *               @OA\Property(property="sexe", type="string"),
    *               @OA\Property(property="profession", type="string"),
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Propriétaire ajouté au logement",
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

    public function ajouterProprietaire(AddProrietaire $formulaire){
        DB::beginTransaction();
        try {
            $logement = $this->logement->logementSpecifique($formulaire->id_logement);
            if(empty($logement)){
                $this->message = "Aucun logement correspondant";
                throw new Exception($this->message, 404);
            }else if($logement->id_gerant != $formulaire->id_gerant){
                $this->message = "Vous n'êtes pas autorisé à effectuer cette opération";
                throw new Exception($this->message, 422);
            }
    
            $proprioData = [
                'nom' => $formulaire->nom,
                'prenom' => $formulaire->prenom,
                'telephone' => $formulaire->telephone,
                'email' => $formulaire->email,
                'date_naissance' => $formulaire->date_naissance,
                'sexe' => $formulaire->sexe,
                'profession' => $formulaire->profession
            ];
    
            $proprietaire = $this->proprio->nouveauProprietaire($proprioData);
            if(!$proprietaire){
                $this->message = "Erreur lors de l'ajout du propriétaire";
                throw new Exception($this->message, 422);
            }
    
            $updateLogement = $this->logement->modifierLogement($formulaire->id_logement, ['id_proprietaire' => $proprietaire->id]);
            if(!$updateLogement){
                $this->message = "Erreur lors de la mise à jour du logement";
                throw new Exception($this->message, 422);
            }
    
            DB::commit();
            Log::info("Ajout du propriétaire effectué");
            return $this->succes(null, "Le propiétaire est ajouté au logement");
            
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 422 || $th->getCode() == 404){
                Log::error($this->message.' fichier: '.$th->getFile().' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error('Erreur inattendue,  fichier: '.$th->getFile().' Ligne: '.$th->getLine().'  message: '.$th->getMessage());
                return $this->echec('Une erreur inattendue est survenue', $th->getCode());
            }
        }
    }

    /**
    * @OA\Post(
    *      path="/logement/ajouter-reaction",
    *      operationId="addReaction",
    *      tags={"Logements"},
    *      summary="Reaction sur un logement",
    *      description="Les clients ont la possibilité de réagir (commentaire, avis ou demande de logement)",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="objecct",
    *               required={"id_logement", "id_compte", "commentaire"},
    *               @OA\Property(property="id_logement", type="string"),
    *               @OA\Property(property="id_compte", type="string"),
    *               @OA\Property(property="commentaire", type="string"),
    *               @OA\Property(property="avis", type="string"),
    *               @OA\Property(property="demande", type="string"),
    *          ),
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Propriétaire ajouté au logement",
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

    public function ajouterReaction(AjouterReactionRequest $request){
        DB::beginTransaction();
        try {
            $logement = $this->logement->logementSpecifique($request->id_logement);
            if(empty($logement)){
                $this->message = "Aucun logement correspondant";
                throw new Exception($this->message, 404);
            }

            $compte = $this->compte->obtenirCompteSpecifique($request->id_compte);
            if(empty($compte)){
                $this->message = "Aucun compte utilisateur trouvé";
                throw new Exception($this->message, 404);
            }

            $reactionData = [
                'code' => codeReaction(3),
                'commentaire' => $request->commentaire,
                'id_client' => $request->id_compte,
                'id_logement' => $request->id_logement,
                'avis' => $request->avis,
                'demande' => $request->demande
            ];

            if(!empty($request->demande) && $request->id_compte != $logement->id_gerant){
                $reactionData['jour_demande'] = now();
            }else if($request->id_compte == $logement->id_gerant){
                $this->message = "Vous ne pouvez pas envoyer une demande pour votre propre logement";
                throw new Exception($this->message, 422);
            }
            $existReaction = $this->reaction->clientReaction($request->id_compte, $request->id_logement);
            if(empty($existReaction)){
                $reaction = $this->reaction->ajouterReaction($reactionData);
                if(!$reaction){
                    $this->message = "Erreur lors de l'ajout de votre réaction";
                    throw new Exception($this->message, 422);
                }
            }else{
                $updateReaction = $this->reaction->modifierReaction($existReaction->id, $reactionData);
                if(!$updateReaction){
                    $this->message = "Erreur lors de la mise à jour de votre réaction";
                    throw new Exception($this->message, 422);
                }
            }

            DB::commit();
            Log::info("Ajout d la réaction ou modification effectuée");
            return $this->succes(null, 'Ajout ou modification de la réation effectuée');
        } catch (\Throwable $th) {
            DB::rollBack();
            if($th->getCode() == 422 || $th->getCode() == 404){
                Log::error($this->message.' Ligne: '.$th->getLine());
                return $this->echec($this->message, $th->getCode());
            }else{
                Log::error("Une erreur inattendue est survenue : ". $th->getFile().' ||| '.$th->getMessage());
                return $this->echec("Une erreur inattendue est survenue", $th->getCode());
            }
        }
    }

    public function listeLogementByGerant(LogementGerantFilterRequest $formulaire){
        DB::beginTransaction();
        try {
            $gerant = $this->compte->obtenirCompteSpecifique($formulaire->id_gerant);
            if(empty($gerant)){
                $this->message = "Aucun compte utilisateur retrouvé";
                throw new Exception($this->message, 404);
            }else if($gerant->id_type_compte != $this::COMPTE_GERANT){
                $this->message = "Vous n'êtes pas éligiblz pour effectuer cette opération";
                throw new Exception($this->message, 422);
            }

            $logements = $this->logement->logementGerantAll($formulaire->id_gerant, $formulaire->type_logement, $formulaire->ville, $formulaire->valeur);
            
            } catch (\Throwable $th) {
            //throw $th;
        }
    }

}
