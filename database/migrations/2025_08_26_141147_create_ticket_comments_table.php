<?php

// database/migrations/2025_08_26_120110_create_ticket_comments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('ticket_comments', function (Blueprint $t) {
      $t->id();
      $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
      $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->text('message');
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('ticket_comments'); }
};
