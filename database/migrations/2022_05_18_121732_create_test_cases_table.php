<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suite_id')->constrained('suites')->onDelete('cascade');
            $table->string("title");
            $table->boolean("automated")->default(false);
            $table->boolean('need_on_smoke')->default(0); // Добавляем столбец с дефолтным значением
            $table->integer("priority")->default(\App\Enums\CasePriority::NORMAL);
            $table->longText("data")->nullable();
            $table->integer("order")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_cases');
    }
}
