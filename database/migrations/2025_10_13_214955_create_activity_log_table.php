<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('log_name')->nullable()->index();
            $table->text('description')->nullable();

            // Het gemuteerde model waarop de activiteit betrekking heeft
            $table->nullableMorphs('subject');   // subject_type, subject_id (+ index)

            // Wie veroorzaakte de activiteit (user, job, etc.)
            $table->nullableMorphs('causer');    // causer_type, causer_id (+ index)

            // Extra metadata
            $table->string('event')->nullable(); // bv. created / updated / deleted
            $table->json('properties');          // gebruik ->text('properties') als je DB geen JSON ondersteunt

            // Batch-id om activiteiten te groeperen (optioneel, maar handig)
            $table->uuid('batch_uuid')->nullable()->index();

            $table->timestamps();

            // Extra index kan query’s versnellen op tijdsgebaseerde views
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
}
