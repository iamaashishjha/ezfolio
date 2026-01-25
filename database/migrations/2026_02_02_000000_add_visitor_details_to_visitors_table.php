<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisitorDetailsToVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('country_code')->nullable()->after('location');
            $table->string('region')->nullable()->after('country_code');
            $table->string('region_code')->nullable()->after('region');
            $table->string('city')->nullable()->after('region_code');
            $table->string('zip')->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('zip');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('timezone')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn([
                'country_code',
                'region',
                'region_code',
                'city',
                'zip',
                'latitude',
                'longitude',
                'timezone',
            ]);
        });
    }
}
