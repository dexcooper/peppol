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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('peppol_provider')->nullable()->after('id');
            $table->uuid('maventa_company_id')->nullable()->after('id');
            $table->uuid('maventa_user_id')->nullable()->after('id');
            $table->string('email')->nullable()->after('vat_number');
            $table->string('contact_person_first_name')->after('vat_number')->nullable();
            $table->string('contact_person_name')->after('vat_number')->nullable();
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('peppol_provider');
            $table->dropColumn('maventa_company_id');
            $table->dropColumn('maventa_user_id');
            $table->dropColumn('email');
            $table->dropColumn('contact_person_first_name');
            $table->dropColumn('contact_person_name');
            $table->dropColumn('street');
            $table->dropColumn('number');
            $table->dropColumn('zip_code');
            $table->dropColumn('city');
            $table->dropColumn('country');
        });
    }
};
