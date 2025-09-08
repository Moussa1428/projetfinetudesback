<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $with = ['responsable.roles', 'createur.roles', 'membres.roles'];
    protected $fillable = [
        'nom',
        'annee',
        'description',
        'responsable_id',
        'created_by',
        'is_active'
    ];

    // Responsable (Admin ou Assistant)
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Créateur du groupe
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Membres du groupe (tous rôles confondus)
    public function membres()
    {
        return $this->belongsToMany(User::class, 'groupe_user')
                    ->withPivot('role_in_groupe')
                    ->withTimestamps();
    }
}
