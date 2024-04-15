<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $table = 'comptes';

    protected $fillable = [
        'id', 'nom', 'prenom', 'date_naissance', 'sexe', 'telephone', 'email', 'profession', 'piece_justificative', 'nationalite', 'num_cni', 'cni_recto', 'cni_verso','id_user', 'created_at', 'updated_at'
    ];

    

    /** 
     * Créer un nouveau compte
     *      Fournir les informations du compte sous forme de tableau
     */
    public function creerCompte(array $inforamtions):Compte{
        return Compte::create($inforamtions);
    }


    /**
     * Retourne les informations d'un compte en base de données à partir de son identifiant
     */
    public function obtenirCompteSpecifique( int $idCompte) {
        return  Compte::find($idCompte);
    }

    public function selectionnerCompteParIdUser(int $idCompte){
        return Compte::select(
            'comptes.id',
            'comptes.nom',
            'comptes.prenom',
            'comptes.date_naissance',
            'comptes.sexe',
            'comptes.telephone',
            'comptes.email',
            'comptes.email',
            'comptes.profession',
            'comptes.id_user',
            'comptes.num_cni',
            'users.id_type_compte',
            'users.activated_account'
        )
        ->join('users', 'comptes.id_user', '=', 'users.id')
        ->where('comptes.id_user', $idCompte)
        ->first();
    }

    public function miseAjourCompte(array $data, int $idCompte){
        return Compte::where('id', $idCompte)->update($data);
    }

}
