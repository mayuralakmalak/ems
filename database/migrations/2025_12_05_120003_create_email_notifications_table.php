<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // e.g., 'exhibitor_registration', 'booth_assignment'
            $table->string('subject_line');
            $table->text('email_body')->nullable();
            $table->json('recipients'); // ['Exhibitor Contact', 'Attendee', etc.]
            $table->enum('category', ['event_triggered', 'system', 'template'])->default('event_triggered');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_notifications');
    }
};
