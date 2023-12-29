<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
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

Route::post('create_account', [AuthController::class, 'createAccount']); //Créé un compte utilisateur dans la table users
Route::get('check_email/{userid}/{codeverification}', [AuthController::class, 'verifieadresse'])->name('verifiemail'); //L'adresse e-mail de l'utilisateur est vérifiée lorsqu'il reçoit un mil contenant le code de vérification
Route::post('check_account_Pass', [AuthController::class, 'verifieByPass']); //Si l'email entré lors de la création du compte est déjà existant, l'utilisateur vérifie si c'est le sien en entrant le mot de passe
Route::post('finaliser_compte', [AuthController::class, 'finaliseAccount']);
Route::post('userlogin', [AuthController::class, 'accountLogin']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
