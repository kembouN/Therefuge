<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logement extends Model
{
    use HasFactory;

    protected $table = 'logements';

    protected $fillable = [
        'id', 'description', 'disponibilite_logement', 'visibilite', 'valeur', 'id_gerant', 'id_proprietaire', 'id_type', 'created_at', 'updated_at'
    ];
}
