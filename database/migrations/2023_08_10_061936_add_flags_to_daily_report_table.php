<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagsToDailyReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_report', function (Blueprint $table) {
            //
            $table->tinyInteger('cctv_flag')->after('report_date')->default(0);
            $table->tinyInteger('biometric_flag')->after('cctv_flag')->default(0);
            $table->tinyInteger('ipPhone1_flag')->after('biometric_flag')->default(0);
            $table->tinyInteger('ipPhone2_flag')->after('ipPhone1_flag')->default(0);
            $table->tinyInteger('ipPhone3_flag')->after('ipPhone2_flag')->default(0);
            $table->tinyInteger('ipPhone4_flag')->after('ipPhone3_flag')->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_report', function (Blueprint $table) {
            //
        });
    }
}
