<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'address',
        'password',
        'is_active',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
    protected $guard_name = 'sanctum';
    protected $with = ['roles'];

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function assistant()
    {
        return $this->hasOne(Assistant::class);
    }
    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }
    public function etudiant()
    {
        return $this->hasOne(Etudiant::class);
    }

    public function groupesCree()
    {
        return $this->hasMany(Groupe::class, 'created_by');
    }

    public function groupesResponsable()
    {
        return $this->hasMany(Groupe::class, 'responsable_id');
    }

    public function groupesMembre()
    {
        return $this->belongsToMany(Groupe::class, 'groupe_user')
            ->withPivot('role_in_groupe')
            ->withTimestamps();
    }
}
