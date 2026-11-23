
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_status_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_record_id')
                ->constrained('attendance_records')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('old_status', [
                'present',
                'late',
                'absent',
                'permission',
                'sick',
            ]);

            $table->enum('new_status', [
                'present',
                'late',
                'absent',
                'permission',
                'sick',
            ]);

            $table->text('reason');

            $table->timestamps();

            $table->index('attendance_record_id');
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_status_changes');
    }
};
