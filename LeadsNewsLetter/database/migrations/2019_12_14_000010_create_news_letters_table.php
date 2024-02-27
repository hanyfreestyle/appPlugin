<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('leads_news_letters', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->integer('export')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('leads_news_letters');
    }
};
