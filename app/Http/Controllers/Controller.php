<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
* @OA\Info(
*     version="0.1",
*     title="API MESHUTE"
* )
*/

/**
* @OA\PathItem(path="/api")
*/

/**
* @OA\Info(
*     version="1.0",
*     title="DOCUMENTATION DE L'API DU PROJET MESHUTE",
*     description= "MESHUTE est un projet de gestion de ressources immobilières qui a pour principal but de rendre leur accès facile",
*     @OA\Contact(
*       email= "knk.towork@gmail.com"
*     ),
*     @OA\License(
*       name="Apache2.0",
*       url="http://www.apache.org/licenses/LICENSE-2.0.html"
*     )
* )
* @OA\Server(
*   url=L5_SWAGGER_CONST_HOST,
*   description = "Url de la documentation swagger Meshute"
* )

* @OA\SecurityScheme(
*   securityScheme = "sanctum",
*   type = "http",
*   scheme = "bearer"
* )
*/

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    const COMPTE_INACTIF = 0;
    const COMPTE_ACTIF = 1;
    const COMPTE_CLIENT = 200;
    const COMPTE_ADMIN = 300;
    const COMPTE_GERANT = 100;
    const FEMININ = 0;
    const MASCULIN = 1;
    const LOGEMENT_VISIBLE = 1;
    const LOGEMENT_DISPONIBLE = 1;
    const LOGEMENT_INVISIBLE = 0;
    protected $message;

    function succes($reponse, $message, $code = 200){
        return response()->json([
            'statut' => true,
            'message' => $message,
            'data' => $reponse
        ]);
    }

    function echec($message, $code){
        return response()->json([

            'statut' => false,
            'message' => $message
        ]);
    }
}
