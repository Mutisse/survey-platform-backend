<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->unique();

            // Campos acadêmicos
            $table->foreignId('university_id')->nullable();
            $table->string('course')->nullable();
            $table->string('bi_number', 20)->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();  // Mudado de enum para string
            $table->string('institution_type')->nullable();
            $table->integer('admission_year')->nullable();
            $table->integer('expected_graduation')->nullable();
            $table->string('academic_level')->nullable();
            $table->string('student_card_number')->nullable();
            $table->json('research_interests')->nullable();

            // Campos existentes
            $table->string('role')->default('participant');
            $table->string('verification_status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('whatsapp_notifications')->default(true);
            $table->jsonb('profile_info')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Índices
            $table->index('role');
            $table->index('verification_status');
            $table->index('university_id');
            $table->index('bi_number');
        });

        // Constraints CHECK (agora incluindo gender)
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('student', 'participant', 'admin'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_verification_status_check CHECK (verification_status IN ('pending', 'approved', 'rejected'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_gender_check CHECK (gender IN ('Masculino', 'Feminino'))");
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
