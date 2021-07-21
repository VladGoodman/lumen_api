<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTriggerForTaskUpdateCountTasks extends Migration
{

    public function up()
    {
        DB::unprepared("
        CREATE TRIGGER update_count
        AFTER INSERT OR DELETE OR UPDATE
        ON public.tasks
        FOR EACH ROW
        EXECUTE FUNCTION public.update_count_tasks();
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER update_count ON public.tasks');
    }
}
