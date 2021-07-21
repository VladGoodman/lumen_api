CREATE TRIGGER update_count
    AFTER INSERT OR DELETE OR UPDATE
    ON public.tasks
    FOR EACH ROW
    EXECUTE FUNCTION public.update_count_tasks();