<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tentatives_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_raccordement')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('methode'); // appel, sms, email, whatsapp...
            $table->string('resultat'); // joignable, injoignable, message_laisse...
            $table->text('notes')->nullable();
            $table->timestamp('effectuee_le')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tentatives_contact');
    }
};
