<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFunctionUpdateCompleted extends Migration
{
    public function up()
    {
        DB::unprepared("
        CREATE OR REPLACE FUNCTION public.update_completed_info()
        RETURNS trigger
        LANGUAGE 'plpgsql'
        AS $$
        DECLARE
            count_tasks INTEGER;
        	count_completed_tasks INTEGER;
        BEGIN

        	IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
        		count_tasks := (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.list_id);
        		count_completed_tasks := (
        			SELECT COUNT(*) FROM public.tasks
        				WHERE list_id = NEW.list_id AND is_completed = true
        		);
        		IF ( count_tasks > 0) THEN
        			IF (count_tasks = count_completed_tasks) THEN
                        UPDATE public.lists SET is_completed = true
                        WHERE lists.id = NEW.list_id;
                    ElSE
                        UPDATE public.lists SET is_completed = false
                        WHERE lists.id = NEW.list_id;
                    END IF;
                ELSE
                    UPDATE public.lists SET is_completed = true
                    WHERE lists.id = NEW.list_id;
                END IF;
        	ELSIF (TG_OP = 'DELETE') THEN
        		count_tasks := (SELECT COUNT(*) FROM public.tasks WHERE list_id = OLD.list_id);
        		count_completed_tasks := (
        			SELECT COUNT(*) FROM public.tasks
        				WHERE list_id = OLD.list_id AND is_completed = true
        		);
        		IF ( count_tasks > 0) THEN
        			IF (count_tasks = count_completed_tasks) THEN
                        UPDATE public.lists SET is_completed = true
                        WHERE lists.id = OLD.list_id;
                    ElSE
                        UPDATE public.lists SET is_completed = false
                        WHERE lists.id = OLD.list_id;
                    END IF;
                ELSE
                    UPDATE public.lists SET is_completed = true
                    WHERE lists.id = OLD.list_id;
                END IF;
            END IF;
            RETURN NULL;
        END;
        $$;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION "update_completed_info"');
    }
}
