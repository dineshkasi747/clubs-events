<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('president_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Add foreign key constraint to users table now that clubs table exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
        });
        Schema::dropIfExists('clubs');
    }
};
