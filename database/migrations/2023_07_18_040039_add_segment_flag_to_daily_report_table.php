<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSegmentFlagToDailyReportTable extends Migration
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
            $table->tinyInteger('segment_flag')->after('report_date')->default(0);
            $table->tinyInteger('mail_flag')->after('segment_flag')->default(0);

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
