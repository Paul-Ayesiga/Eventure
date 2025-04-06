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
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');

            // Registration Settings
            $table->dateTime('registration_deadline')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->boolean('enable_waitlist')->default(false);
            $table->integer('waitlist_capacity')->nullable();

            // Access Control
            $table->boolean('is_private')->default(false);
            $table->string('access_code', 255)->nullable();

            // Payment Settings
            $table->boolean('enable_refunds')->default(false);
            $table->dateTime('refund_deadline')->nullable();
            $table->boolean('enable_discounts')->default(false);
            $table->integer('max_discounts_per_booking')->nullable();

            // Event Features
            $table->boolean('enable_live_streaming')->default(false);
            $table->string('streaming_platform', 255)->nullable();
            $table->string('streaming_url', 255)->nullable();

            // Analytics
            $table->boolean('enable_analytics')->default(false);
            $table->boolean('track_attendance')->default(false);
            $table->boolean('track_engagement')->default(false);
            $table->boolean('track_revenue')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_settings');
    }
};
