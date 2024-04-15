<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'id','contenu', 'lisibilite', 'lecture', 'modification', 'fichier', 'id_conversation', 'envoyeur', 'receveur', 'created_at', 'updated_at'
    ];

}
