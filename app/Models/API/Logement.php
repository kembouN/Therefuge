<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Logement extends Model
{
    use HasFactory;

    protected $table = 'logements';

    protected $fillable = [
        'id','code', 'description', 'disponibilite_logement', 'largeur', 'longueur', 'visibilite', 'valeur', 'id_gerant', 'id_proprietaire', 'id_type_logement', 'id_localisation', 'created_at', 'updated_at'
    ];


    public function logementSpecifique(int $idLogement){
        return Logement::find($idLogement);
    }

    public function ajouterLogement(array $logement){
        return Logement::create($logement);
    }

    public function modifierLogement (int $idLogement, array $logement){
        return Logement::where('id', $idLogement)->update($logement);
    }

    public function logementGerantAll(int $idGerant, int $typeLogement, $ville, int $valeur){
        $requete =  DB::table('vue_logements')->select(
            'id_log',
            'code_log',
            'description_log',
            'disponibilite_log',
            'visibilite_log',
            'valeur_log',
            'largeur_log',
            'longueur_log',
            'gerant_log',
            'id_localisation',
            'pays',
            'ville',
            'latitude',
            'longitude',
            'quartier',
            'id_type_logement',
            'libelle',
            'photo_logement',
            'reaction_logement'
        )
        ->where(function($query) use ($typeLogement, $valeur, $ville){
            if(isset($typeLogement)){
                $query->where('id_type_logement', $typeLogement);
            }

            if(isset($ville)){
                $query->orWhereRaw('LOWER(ville) LIKE ?', ["%". strtolower($ville). "%"]);
            }

            if(isset($valeur)){
                $query->whereRaw('valeur' , '>=', $valeur);
            }
        })
        ->where('gerant_log', $idGerant)
        ->get();
        return $requete;
    }
    
}
