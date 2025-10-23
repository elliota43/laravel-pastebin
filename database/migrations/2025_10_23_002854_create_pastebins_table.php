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
        Schema::create('pastebins', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->text('content');
            $table->string('language', 50);
            $table->integer('expires_at')->nullable();
            $table->enum('visibility', ['public', 'unlisted', 'private']);
            $table->string('hash', 16)->unique()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pastebins');
    }
};
