<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpCountToSiteInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_info', function (Blueprint $table) {
            //
            $table->string('employee_count')->after('ext')->nullable();
            $table->string('attendance_mode')->after('employee_count')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_info', function (Blueprint $table) {
            //
        });
    }
}
