<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompteController;
use App\Http\Controllers\API\LogementController;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/* Routes pour le test de la création du compte et le login*/

Route::post('register', [AuthController::class, 'registerTest']);
Route::post('login', [AuthController::class, 'loginTest']);

/*Fin test*/


/**Gestion de compte utilisateur */

Route::post('users/create_account', [AuthController::class, 'createAccount']); //Créé un compte utilisateur dans les tables users et comptes
Route::get('user/{user_id}/check_email/{codeverification}', [AuthController::class, 'verifierEmail'])->name('verifiemail'); //L'adresse e-mail de l'utilisateur est vérifiée lorsqu'il reçoit un mail contenant le code de vérification
Route::post('login', [AuthController::class, 'accountLogin']);
Route::post('user/reset-password', [AuthController::class, 'reinitialiserMotDePasse']);

Route::middleware('auth:sanctum')->group(function() {

    /** Gestion du gérant */
    Route::controller(CompteController::class)->group(function(){
        // Route::apiResource('gerant', CompteController::class);
        Route::put('update-info/account/{id_compte}', 'update');
    });

    /**gestion des logements */
    Route::controller(LogementController::class)->group(function(){
        Route::apiResource('logement', LogementController::class);
        Route::get('logement/{id_logement}/changer-visibilite', 'visibiliteOrNot');
        Route::post('logement/ajouter-proprio', 'ajouterProprietaire');
        Route::get('logements/gerant/{idgerant}', 'index');
        Route::post('logement/ajouter-reaction', 'ajouterReaction');
    });


    Route::controller(AuthController::class)->group(function(){
        Route::post('user/change-password', 'changerMotDePasse');
        Route::get('user/{user_id}/account-activate', 'activerCompte');
    });

});
