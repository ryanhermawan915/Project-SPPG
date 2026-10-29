
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->string('academic_year', 20);
            $table->enum('semester', ['odd', 'even']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);

            $table->unique(
                ['academic_year', 'semester'],
                'academic_periods_year_semester_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropUnique('academic_periods_year_semester_unique');
            $table->dropColumn([
                'academic_year',
                'semester',
                'start_date',
                'end_date',
                'is_active',
            ]);
        });
    }
};
