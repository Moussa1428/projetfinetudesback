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

    protected static function booted()
    {
        static::deleting(function ($classe) {
            foreach ($classe->etudiants as $etudiant) {
                // Supprimer le user lié à l'étudiant
                if ($etudiant->user) {
                    $etudiant->user->delete();
                }
                // Supprimer l'étudiant
                $etudiant->delete();
            }
        });
    }
}
