<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTriggerForTaskUpdateCompleted extends Migration
{

    public function up()
    {
        DB::unprepared("
        CREATE TRIGGER update_completed
        AFTER INSERT OR DELETE OR UPDATE
        ON public.tasks
        FOR EACH ROW
        EXECUTE FUNCTION public.update_completed_info();
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER update_completed ON public.tasks');
    }
}
