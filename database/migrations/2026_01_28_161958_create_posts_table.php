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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); //Autor
            $table->string('title', 200);
            $table->string('slug', 220)->unique;

            //Lühike sissejuhatus
            $table->text('intro')->nullable();
            //Pilt
            $table->string('featured_image_path')->nullable();
            //Postituse põhisisu
            $table->longText('body_html');

            // Kui null, siis on postitus mustand
            $table->timestamp('published_at')->nullable()->index();

            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
