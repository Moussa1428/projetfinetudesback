<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sender_id');   // User qui envoie
            $table->string('target_type');             // "admin", "assistant", "enseignant", "classe", "groupe"
            $table->unsignedBigInteger('target_id')->nullable(); // null si "all" ou classe/groupe
            $table->text('message');
            $table->timestamps();


            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
