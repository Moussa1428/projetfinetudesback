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
        'status',
        'created_by',
        'responsable_id',
    ];

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }



}
