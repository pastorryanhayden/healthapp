<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weigh_ins', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('pounds', 6, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weigh_ins');
    }
};
