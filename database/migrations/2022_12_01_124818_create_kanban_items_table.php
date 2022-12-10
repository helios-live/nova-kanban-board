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
    Schema::create('kanban_items', function (Blueprint $table) {
      $table->id();
      $table->smallInteger('order')->default(0);
      $table->foreignId('kanban_board_id')->constrained();
      $table->foreignId('kanban_column_id')->constrained();
      $table->string('title');
      $table->nullableMorphs('target');
      $table->timestamps();

      $table->unique(['kanban_board_id', 'target_type', 'target_id', 'title']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('kanban_items');
  }
};