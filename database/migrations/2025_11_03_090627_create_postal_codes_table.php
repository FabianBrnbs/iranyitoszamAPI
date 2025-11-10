<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postal_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->index();
            $table->string('settlement'); // település neve
            $table->foreignId('county_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['code', 'settlement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postal_codes');
    }
};