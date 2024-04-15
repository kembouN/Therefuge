<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;
    protected $table = 'contrats';

    protected $fillable = [
        'id', 'type_contrat', 'description_contrat', 'id_client', 'valeur_totale', 'dure', 'date_expiration', 'id_logement', 'created_at', 'updated_at'
    ];
}
