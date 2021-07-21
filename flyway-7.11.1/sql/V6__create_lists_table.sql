CREATE TABLE IF NOT EXISTS public.lists
(
    id bigint NOT NULL GENERATED ALWAYS AS IDENTITY ( INCREMENT 1 START 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1 ),
    name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    count_tasks integer NOT NULL DEFAULT 0,
    is_completed boolean NOT NULL,
    is_closed boolean NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT lists_pkey PRIMARY KEY (id)
)

