<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateAccountRequest;
use App\Http\Requests\API\UpdateCompteRequest;
use App\Http\Requests\API\UpdateGerantRequest;
use App\Models\API\Compte;
use Illuminate\Http\Request;
use App\Models\API\Gerant;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CompteController extends Controller
{
    private $compte;
    private $user;


    public function __construct(){
        $this->compte = new Compte();
        // $this->user = new User();
    }


    public function index(){
        //
    }

    public function show($idgerant){

        if( Gate::allows('autorise'))
        $specificgerant = $this->compte->getGerant($idgerant);
        if(!$specificgerant){
            return $this->echec('Aucun utilisateur ne correspond à cet identifiant', 422);
        }
        return $this->succes($specificgerant,'Utilisateur retrouvé');
    }

    

    public function store(Request $request){
        //
    }

    /**
    * @OA\Put(
    *      path="/update-info/account/{id_compte}",
    *      operationId="updateAccountData",
    *      tags={"Compte"},
    *      security={"sanctum":{{}}},
    *      summary="Mise à jour des informations personnelles de l'utilisateur",
    *      description="La gestion du compte utilisateur est principalement centrée sur la mise à jour des informations personnelles",
    *      @OA\Parameter(
    *          description="Identifiant du compte utilisateur",
    *          in="path",
    *          name="id_compte",
    *          required=true,
    *          @OA\Schema(type="string"),
    *      ),
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\JsonContent(
    *               type="object",
    *               required={"nom", "prenom", "date_naissance", "sexe", "telephone"},
    *               @OA\Property(property="nom", type="string"),
    *               @OA\Property(property="prenom", type="string"),
    *               @OA\Property(property="date_naissance", type="string"),
    *               @OA\Property(property="sexe", type="string"),
    *               @OA\Property(property="telephone", type="string"),
    *               @OA\Property(property="email", type="string"),
    *               @OA\Property(property="num_cni", type="string"),
    *               @OA\Property(property="cni_recto", type="string"),
    *               @OA\Property(property="cni_verso", type="string"),
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

    public function update(UpdateCompteRequest $request, $idCompte){
        DB::beginTransaction();
        try {
            $gerant = $this->compte->obtenirCompteSpecifique($idCompte);
            if(empty($gerant)){
                $this->message = "Aucun compte ne correspond à l'identifiant";
                throw new Exception($this->message, 404);
            }
    
            $compteUpdate = $this->compte->miseAjourCompte($request->all(), $idCompte);
            if(!$compteUpdate){
                $this->message = "Erreur survenue lors de la mise à jour de vos informations";
                throw new Exception($this->message, 422);
            }

            DB::commit();
            Log::info("Informations du compte n°$idCompte, mises à jour");
            return $this->succes(null, "Informations de votre compte mises à jour");
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

}
