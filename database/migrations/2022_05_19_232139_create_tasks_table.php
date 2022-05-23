<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger("board_id");
                $table->string('title')->nullable();
                $table->integer('task_order')->nullable();
                $table->timestamps();

                $table->foreign("board_id")->references("id")->on("boards");
            });
        } else {
            if (!Schema::hasColumn("tasks", "created_at")) {

                Schema::table("tasks", function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
