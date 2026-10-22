
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_record_id')
                ->constrained('attendance_records')
                ->cascadeOnDelete();

            $table->enum('event_type', [
                'check_in',
                'check_out',
            ]);

            $table->timestamp('recorded_at');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('verification_method', [
                'face_recognition',
                'manual',
            ])->default('face_recognition');

            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['attendance_record_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
