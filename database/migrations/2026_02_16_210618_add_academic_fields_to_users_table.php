<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adicionar campos acadêmicos
            $table->string('bi_number', 20)->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('institution_type')->nullable();
            $table->integer('admission_year')->nullable();
            $table->integer('expected_graduation')->nullable();
            $table->string('academic_level')->nullable();
            $table->string('student_card_number')->nullable();
            $table->json('research_interests')->nullable();

            $table->index('bi_number');
        });

        // Adicionar constraint CHECK para gender
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_gender_check CHECK (gender IN ('Masculino', 'Feminino'))");
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bi_number',
                'birth_date',
                'gender',
                'institution_type',
                'admission_year',
                'expected_graduation',
                'academic_level',
                'student_card_number',
                'research_interests'
            ]);
        });

        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_gender_check");
    }
};
