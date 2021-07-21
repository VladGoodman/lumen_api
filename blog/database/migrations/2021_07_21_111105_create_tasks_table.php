<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->unsignedBigInteger('executor_user_id');
            $table->unsignedBigInteger('list_id');
            $table->foreign('executor_user_id')->references('id')->on('users');
            $table->foreign('list_id')->references('id')->on('lists');
            $table->text("description")->nullable();
            $table->boolean("is_completed");
            $table->smallInteger('urgency');
            $table->timestamps();
        });
        DB::unprepared("
            ALTER TABLE tasks ADD CONSTRAINT asks_urgency_check CHECK (urgency >= 1 AND urgency <= 5)
        ");
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
