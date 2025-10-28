<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3b82f6'); // Hex color
            $table->integer('position')->default(0);
            $table->timestamps();
            
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
