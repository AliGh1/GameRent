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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->text('description')->nullable();
            $table->string('image_url');
            $table->unsignedInteger('price_z2_weekly');
            $table->unsignedInteger('price_z3_weekly');
            $table->date('release_date');
            $table->string('publisher', 100);
            $table->string('developer', 100);
            $table->string('modes', 100);
            $table->string('age_rating', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
