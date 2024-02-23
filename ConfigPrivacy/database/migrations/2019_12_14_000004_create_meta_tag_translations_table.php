<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('config_meta_tag_translations', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('meta_tag_id')->unsigned();
            $table->string('locale')->index();
            $table->string('g_title')->nullable();
            $table->text('g_des')->nullable();
            $table->unique(['meta_tag_id','locale']);
            $table->foreign('meta_tag_id')->references('id')->on('config_meta_tags')->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('config_meta_tag_translations');
    }
};
