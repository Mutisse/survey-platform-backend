<?php
// database/migrations/2024_01_01_000016_create_survey_institutions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation');
            $table->string('type');
            $table->string('logo_url')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->integer('survey_count')->default(0);
            $table->timestamps();

            $table->index('abbreviation');
            $table->index('type');
            $table->index('is_verified');
            $table->index('survey_count');

            // Check constraint for institution types
            $table->check("type IN ('university', 'college', 'research_center', 'company', 'ngo', 'government')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_institutions');
    }
};
