CREATE TRIGGER update_completed
    AFTER INSERT OR DELETE OR UPDATE
    ON public.tasks
    FOR EACH ROW
    EXECUTE FUNCTION public.update_completed_info();