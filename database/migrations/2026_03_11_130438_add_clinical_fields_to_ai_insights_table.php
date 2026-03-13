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
        Schema::table('ai_insights', function (Blueprint $table) {
            $table->text('red_flags')->nullable();
            $table->string('case_severity')->nullable();
            $table->text('brief_description')->nullable();
            $table->text('possible_diagnoses')->nullable()->change();
            $table->text('suggested_cid_codes')->nullable();
            $table->text('suggested_exams')->nullable();
            $table->text('suggested_conducts')->nullable();
            $table->text('missing_clinical_information')->nullable();

            $table->dropColumn([
                'identified_symptoms',
                'main_topics'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_insights', function (Blueprint $table) {
            $table->dropColumn([
                'red_flags',
                'case_severity',
                'brief_description',
                'suggested_cid_codes',
                'suggested_exams',
                'suggested_conducts',
                'missing_clinical_information'
            ]);

            $table->string('possible_diagnoses')->nullable()->change();
        });
    }
};
