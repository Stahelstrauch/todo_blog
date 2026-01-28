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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')->constrained()->cascadeOnDelete(); // seos postitusega
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Postituse autor
            $table->text('comment');
            $table->boolean('is_hidden')->default(false)->index(); //vaikimisi on kommentaarid avalikud
            $table->string('ip_address', 45)->nullable(); //Ip aadress kasutajal, spämi jaoks

            $table->timestamps();
            $table->index(['post_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
