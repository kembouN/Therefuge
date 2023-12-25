<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


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
