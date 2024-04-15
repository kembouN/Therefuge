<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $table = 'reactions';

    protected $fillable = [
        'id','code', 'commentaire', 'id_client', 'id_logement', 'avis', 'demande', 'jour_demande', 'jour_annulation_demande', 'demande_rejete', 'jour_rejet_ou_accept', 'created_at', 'updated_at'
    ];

    public function getAllReaction($idlogement){
        return Reaction::where('publication', $idlogement)->get();
    }

    public function ajouterReaction(array $data){
        return Reaction::create($data);
    }

    public function modifierReaction(int $id, array $data){
        return Reaction::where('id', $id)->update($data);
    }

    public function clientReaction(int $idClient, int $idLogement){
        return Reaction::where('id_client', $idClient)->where('id_logement', $idLogement)->first();
    }


}
