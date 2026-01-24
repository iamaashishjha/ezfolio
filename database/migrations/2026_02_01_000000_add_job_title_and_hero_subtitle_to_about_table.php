<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobTitleAndHeroSubtitleToAboutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('about', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('name');
            $table->text('hero_subtitle')->nullable()->after('taglines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('about', function (Blueprint $table) {
            $table->dropColumn('job_title');
            $table->dropColumn('hero_subtitle');
        });
    }
}
