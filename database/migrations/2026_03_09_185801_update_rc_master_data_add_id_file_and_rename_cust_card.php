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
        Schema::table('RC_master_data', function (Blueprint $table) {
            $table->string('id_file', 255)->nullable()->after('id');
        });
        Schema::table('RC_master_data', function (Blueprint $table) {
            $table->renameColumn('src_cust_card', 'cust_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('RC_master_data', function (Blueprint $table) {
            $table->renameColumn('cust_card', 'src_cust_card');
        });
        Schema::table('RC_master_data', function (Blueprint $table) {
            $table->dropColumn('id_file');
        });
    }
};
