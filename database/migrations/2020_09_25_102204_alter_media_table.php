<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            DB::statement("ALTER TABLE media CHANGE COLUMN type type enum('image', 'video', 'audio', 'powerpoint', 'code', 'zip', 'text', 'other')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            DB::statement("ALTER TABLE media CHANGE COLUMN type type enum('image', 'video', 'audio', 'txt', 'excel', 'word', 'pdf', 'ppt')");
        });
    }
}
