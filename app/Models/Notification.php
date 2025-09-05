<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'sender_id', 'target_type', 'target_id', 'message', 'read_by'
    ];

    protected $casts = [
        'read_by' => 'array', // JSON converti automatiquement en tableau PHP
    ];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }


}
