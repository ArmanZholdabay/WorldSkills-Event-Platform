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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('title', 255);
            $table->mediumText('description')->nullable();
            $table->string('speaker', 45)->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->enum('type', ['talk', 'workshop']);
            $table->decimal('cost', 9, 2)->nullable();
            $table->timestamps();
            
            $table->index('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
