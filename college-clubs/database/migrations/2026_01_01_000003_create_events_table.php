<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('venue')->nullable(); // For upcoming events
            $table->string('place')->nullable(); // For historical parsed events
            $table->dateTime('start_time')->nullable(); // Nullable for historical events
            $table->dateTime('end_time')->nullable(); // Nullable for historical events
            $table->string('date_string')->nullable(); // For holding single-day historical dates e.g. "12-10-2023"
            $table->string('volunteers')->nullable(); // Holds volunteer count for historical events
            $table->decimal('price', 8, 2)->default(0.00);
            $table->string('status')->default('active'); // active, draft, completed, cancelled
            $table->integer('capacity')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
