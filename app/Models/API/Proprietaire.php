<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proprietaire extends Model
{
    use HasFactory;

    protected $table = 'proprietaires';

    protected $fillable = [
        'id','nom', 'prenom', 'telephone', 'email', 'profession', 'date_naissance', 'sexe', 'created_at', 'updated_at'
    ];

    public function nouveauProprietaire(array $proprietaire){
        return Proprietaire::create($proprietaire);
    }
}
