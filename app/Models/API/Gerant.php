<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Gerant extends Model
{
    use HasFactory;

    protected $table = 'gerants';

    protected $fillable = [
        'id', 'nom', 'prenom', 'date_naissance', 'lieu_naissance', 'sexe', 'telephone', 'email', 'lieu_residence', 'profession', 'piece_justificative', 'proprietaire', 'nationalite', 'cni_recto', 'cni_verso', 'created_at', 'update_at'
    ];


    public function createGerant($data){
        return DB::table('users')->select('*')->where('users.email', $data['email'])->first() ? Gerant::created($data) : false;
    }

    public function allGerant(){
        return Gerant::all();
    }

    public function getGerant($idgerant){
        return Gerant::where('gerants.id', $idgerant)
        ->leftjoin('logements', 'gerants.id', 'logements.id_gerant')
        ->get();
    }

    public function update_gerant($idgerant, $data){
        return Gerant::where('gerants.id',$idgerant)->update($data);
    }
}
