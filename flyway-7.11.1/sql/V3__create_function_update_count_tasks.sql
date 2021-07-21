CREATE FUNCTION public.update_count_tasks()
    RETURNS trigger
    LANGUAGE 'plpgsql'
AS $BODY$
BEGIN
    IF (TG_OP = 'DELETE') THEN
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = OLD.list_id)
        WHERE lists.id = OLD.list_id;
    ELSIF (TG_OP = 'INSERT') THEN
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.list_id)
        WHERE lists.id = NEW.list_id;
    ELSIF (TG_OP = 'UPDATE') THEN
        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = NEW.list_id)
        WHERE lists.id = NEW.list_id;

        UPDATE public.lists
        SET count_tasks = (SELECT COUNT(*) FROM public.tasks WHERE list_id = OLD.list_id)
        WHERE lists.id = OLD.list_id;
    END IF;
    RETURN NEW;
END;
$BODY$;
