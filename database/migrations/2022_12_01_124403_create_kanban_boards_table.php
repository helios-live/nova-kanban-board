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
    Schema::create('kanban_boards', function (Blueprint $table) {
      $table->id();
      // $table->foreignId('team_id')->constrained();
      $table->string('title');
      $table->json('model')->nullable();
      $table->json('model_filter')->nullable();
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
    Schema::dropIfExists('kanban_boards');
  }
};