<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $table = 'photos';

    protected $fillable = [
        'id','photo', 'id_logement', 'id_compte', 'created_at', 'updated_at'
    ];

    public function ajouterPhoto(array $photo){
        return Photo::create($photo);
    }

}
