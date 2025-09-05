<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'sender_id', 'target_type', 'target_id', 'message', 'read_by', 'scheduled_at', 'is_sent'
    ];

     protected $casts = [
        'read_by' => 'array',
        'scheduled_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }


}
