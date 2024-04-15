<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeLogement extends Model
{
    use HasFactory;

    protected $table = 'type_logements';

    protected $fillable = [
        'id', 'libelle', 'created_at', 'updated_at'
    ];
}
