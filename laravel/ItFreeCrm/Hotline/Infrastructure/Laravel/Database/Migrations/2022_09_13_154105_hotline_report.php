<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hotline_reports')) {
            Schema::create('hotline_reports', function (Blueprint $table) {
                $table->id();
                $table->string('product_id', 200);
                $table->string('url_path', 200);
                $table->string('title', 100);

                $table->string('price', 10);
                $table->string('min_price', 10);
                $table->string('leader_price', 10);

                /*$table->unsignedDouble('price');
                $table->unsignedDouble('min_price');
                $table->unsignedDouble('leader_price');*/
                $table->timestamp('created_at')->useCurrent();
                $table->json('shops')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotline_reports');
    }
};
