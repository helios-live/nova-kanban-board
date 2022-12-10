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
    Schema::create('kanban_columns', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->smallInteger('order')->default(0);
      $table->foreignId('kanban_board_id')->constrained();
      $table->string('target_property_value')->nullable();
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
    Schema::dropIfExists('kanban_columns');
  }
};