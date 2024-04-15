<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localisation extends Model
{
    use HasFactory;

    protected $table = 'localisations';

    protected $fillable = [
        'id', 'pays', 'ville', 'latitude', 'longitude', 'quartier', 'rue', 'created_at', 'updated_at'
    ];

    public function nouvelleLocalisation(array $localisation){
        return Localisation::create($localisation);
    }

    public function obtenirLocalisation(int $idLocalisation){
        return Localisation::find($idLocalisation);
    }

    public function modifierLocalisation(int $idLocalisation, array $localisation){
        return Localisation::where('id', $idLocalisation)->update($localisation);
    }
}
