<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateAccountRequest;
use App\Mail\API\SendVerificationCode;
use App\Models\API\Gerant;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Nette\Utils\Random;

class AuthController extends Controller
{
    private $user;
    private $gerant;

    public function __construct(){
        $this->user = new User();
        $this->gerant = new Gerant();
    }


    //****************************************************************************
    //*           PARTIE ACCES ADMINISTRATEUR DEBUT                              *
    //****************************************************************************

    public function registerTest(Request $request){
        $registerform = Validator::make([
            'email' => 'required|email',
            'password' =>'required|confirmed|regex:^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&~|_-€£¥©])[A-Za-z\d@$!%*#?&]{8,20}$'
        ],[
            'email.required' => 'Entrez votre adresse e-mail',
            'email.email' => 'Vérifier votre adresse e-mail',

            'password.required' => 'Le mot de passe est requis',
            'password.confirmed' => 'Veuillez confirmez le mot de passe',
            'password.regex' => 'Le mot de passe contient des caractères non pris en charge'
        ]);

        if($registerform->fails()){
            return $this->echec($registerform->errors(), 422);
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['trust_email'] = Random::generate(6, '0-9');
        $user = User::create($input);
        // Mail::to($user->email)->send(new SendVerificationCode($user));
        $sucess['token'] = $user->createToken('adminAccount')->plainTextToken;
        $sucess['email'] = $user->email;
        return $this->succes($sucess, 'Compte créé avec succès');
    }


    public function loginTest(Request $request){
        $loginrequest = Validator::make([
            'email' => 'required|email',
            'password' => 'required'
        ],[
            'email.required' => 'Entrez votre adresse mail',
            'email.email' => 'Vérifiez l\'email',

            'password.required' => 'Entrez le mot de passe'
        ]);

        if($loginrequest->fails()){
            return $this->echec($loginrequest->errors(), 422);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            return [
                'user' => $user,
                'token' => $user->createToken('adminconnected')->plainTextToken
            ];
        }
        return 'Vérifiez votre email ou votre mote de passe';
    }

    //****************************************************************************
    //*           PARTIE ACCES ADMINISTRATEUR FIN                              *
    //****************************************************************************


    public function createAccount(Request $formulaire){
        $validation = Validator::make([
            'email' => 'required|email|unique:users,email',
            'password' =>'required|confirmed|regex:^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&~|_-€£¥©])[A-Za-z\d@$!%*#?&]$|min:8|max:20',
        ],[
            'email.required' => 'L\'adresse e-mail est requise',
            'email.email' => 'Votre adresse n\'est pas conforme',
            'email.unique' => 'Cette adresse e-mail existe déjà pour un autre compte',

            'password.required' => 'Le mot de passe est requis',
            'password.confirmed' => 'La cconfimation de mot de passe est incorrecte',
            'password.regex' => 'Le mot de passe ne respecte pas la norme ou contient des caractères non autorisés',
            'password.min' => 'Le mot de passe doit contenir au moins huit(8) caractères',
        ]);

        if($validation->fails()){
            return $this->echec($validation->errors(), 422);
        }

        $input = $formulaire->all();
        $input['password'] = Hash::make($input['password']);
        $input['trust_email'] = Random::generate(6, '0-9');
        $user = User::create($input);
        $username = explode('@', $user->email);
        $mailData = [
            'username' => $username[0],
            'user' =>$user
        ];
        Mail::to($user->email)->send(new SendVerificationCode($mailData));
        // $sucess['token'] = $user->createToken('siteToken')->plainTextToken;
        // $sucess['email'] = $user->email;
        return 'Compte créé avec succès';
    }

    public function verifieadresse($iduser, $codeverification){
        $user = User::find($iduser);
        $datecreation = Carbon::parse($user->created_at)->timestamp;
        $dateActuelle = Carbon::now()->timestamp;
        $datelimite = $datecreation + 3600;
        if($dateActuelle > $datelimite){
            return $this->echec('Le code a expiré, vous pouvez en regénérer un nouveau', 422);
        }else if($dateActuelle <= $datelimite && $user->trust_email == $codeverification){
            User::where('id', $iduser)->update(['email_verified_at' => date('Y-m-d H:i:s',$dateActuelle)]);
            $reponse = [
                'user' => User::find($iduser),
                'token' => $user->createToken('userAccount')->plainTextToken
            ];
            return $this->succes($reponse, 'Compte vérifié');
        }
        return $this->echec('Le code ne correspond pas, veuillez le vérifier', 422);
    }

    public function verifieByPass(Request $request){
        $champ = $request->validate([
            'email' => 'required|email',
            'password' =>'required|confirmed|regex:^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&~|_-€£¥©])[A-Za-z\d@$!%*#?&]$|min:8|max:20',

        ]);

        $user = User::where('email',$champ['email'])->first();
        if(!Hash::check($champ['password'], $user->password)){
            return $this->echec('Mot de passe incorrect', 422);
        }

        $reponse = [
            'user' => $user,
            'token' => $user->createToken('userAccount')->plainTextToken
        ];

        return $this->succes($reponse, 'Le mot de passe correspond');

    }

    public function finaliseAccount(CreateAccountRequest $formulairecompte){

        $gerant = [
            'nom' => $formulairecompte->nom,
            'prenom' => $formulairecompte->prenom,
            'date_naissance' => $formulairecompte->date_naissance,
            'lieu_naissance' => $formulairecompte->lieu_naissance,
            'sexe' => $formulairecompte->sexe,
            'telephone' => $formulairecompte->telephone,
            'email' => $formulairecompte->email,
            'lieu_residence' => $formulairecompte->lieu_residence,
            'profession' => $formulairecompte->profession,
            'piece_justificative' => $formulairecompte->piece_justificative,
            'proprietaire' => $formulairecompte->proprietaire,
            'nationalite' => $formulairecompte->nationalite,
            'cni_recto' => $formulairecompte->cni_recto,
            'cni_verso' => $formulairecompte->cni_verso,
            'num_cni' => $formulairecompte->num_cni
        ];

        $newUser = $this->gerant->createGerant($gerant);
        if(!$newUser){
            return $this->echec('Erreur survenue, informations non enregistrées. Rassurer vous de disposer d\'un compte au préalable', 422);
        }
        return $this->succes($newUser, 'Informations enregistrées');

    }

    public function accountLogin(Request $request){
        $validator = Validator::make([
            'email' => 'required|email',
            'password' =>'required|regex:^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&~|_-€£¥©])[A-Za-z\d@$!%*#?&]$|min:8|max:20',
        ],[
            'email.required' => 'L\'adresse e-mail est requise',
            'email.email' => 'Votre adresse n\'est pas conforme',

            'password.required' => 'Le mot de passe est requis',
            'password.regex' => 'Le mot de passe ne respecte pas la norme ou contient des caractères non autorisés',
            'password.min' => 'Le mot de passe doit contenir au moins huit(8) caractères',
        ]);
        
        if($validator->fails()){
            return $this->echec($validator->errors(), 422);
        }

        $user = User::select('*')
        ->where('email', $request->email)
        ->join('gerants', 'users.email', 'gerants.email')
        ->first();

        if(!$user || !Hash::check($request['password'], $user->password)){
            return $this->echec('Le mot de passe ou l\'email est incorrect', 422);
        }

        $retour = [
            'user' => $user,
            'token' => $user->createToken('loginSiteToken')->plainTextToken,
        ];

        return $this->succes($retour, 'Heureux de vous revoir'. $user->nom);
    }
}
