
CREATE TABLE public.tasks
(
    id bigint NOT NULL GENERATED ALWAYS AS IDENTITY ( INCREMENT 1 START 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1 ),
    name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    list_id bigint NOT NULL,
    executor_user_id bigint NOT NULL,
    is_completed boolean NOT NULL,
    description text COLLATE pg_catalog."default",
    urgency integer NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT tasks_pkey PRIMARY KEY (id),
    CONSTRAINT executor_user_id FOREIGN KEY (executor_user_id)
    REFERENCES public.users (id) MATCH SIMPLE
                         ON UPDATE NO ACTION
                         ON DELETE NO ACTION
    NOT VALID,
    CONSTRAINT list_id FOREIGN KEY (list_id)
    REFERENCES public.lists (id) MATCH SIMPLE
                         ON UPDATE NO ACTION
                         ON DELETE NO ACTION
    NOT VALID,
    CONSTRAINT tasks_urgency_check CHECK (urgency >= 1 AND urgency <= 5)
)

