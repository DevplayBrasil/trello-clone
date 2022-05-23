<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('boards')){
            
            Schema::create('boards', function (Blueprint $table) {
                $table->id();
                $table->string("ref");
                $table->longText("title")->nullable();
                $table->string("class")->nullable();
                $table->string("drag_to")->nullable();
                $table->integer("board_order")->nullable();
                $table->timestamps();
            });

        } else {

            if (!Schema::hasColumn("boards", "created_at")) {

                Schema::table("boards", function(Blueprint $table) {
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
        Schema::dropIfExists('boards');
    }
}
