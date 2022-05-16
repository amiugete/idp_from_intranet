-- DROP SCHEMA utils;
CREATE SCHEMA utils AUTHORIZATION gisamiu;

-- tabella dove salvare gli url delle applicazioni su cui fare il redirect
CREATE TABLE utils.proc (
	id serial NOT NULL,
	descrizione varchar,
	url varchar NULL,
	CONSTRAINT proc_pk PRIMARY KEY (id)
);

-- tabella con gli utenti delle varie applicazioni
CREATE TABLE utils.users (
	id serial NOT NULL,
	id_proc int NOT NULL,
	intranet_username varchar NOT NULL,
	role varchar NOT NULL,
	CONSTRAINT users_pk PRIMARY KEY (id),
	CONSTRAINT users_fk FOREIGN KEY (id_proc) REFERENCES utils.proc(id)
);

-- tabella con il log degli accessi
CREATE TABLE utils.log_accessi (
	id serial NOT NULL,
	id_user int NOT NULL,
	id_proc int NOT NULL,
	"time" timestamp NOT NULL DEFAULT now(),
	CONSTRAINT log_accessi_pk PRIMARY KEY (id)
);


-- tabella con il log degli accessi negati
CREATE TABLE utils.log_accessi_negati (
	id serial NOT NULL,
	crypt_user varchar NOT NULL,
	id_proc int NOT NULL,
	"time" timestamp NOT NULL DEFAULT now(),
	CONSTRAINT log_accessi_negatipk PRIMARY KEY (id)
);