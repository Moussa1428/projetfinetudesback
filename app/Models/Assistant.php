<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assistant extends Model
{
    protected $fillable = ['user_id', 'admin_id'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // l'assistant
    }
    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id'); // l'admin lié
    }
}
