<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'niveau',
        'filiere',
        'anneeacademique',
        'code',
        'status'
    ];

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }
}
