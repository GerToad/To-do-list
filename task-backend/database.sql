CREATE DATABASE IF NOT EXISTS tasks;
USE tasks;

CREATE TABLE user(
	id 				int(255) auto_increment not null,
	name 			varchar(255) not null,
	surname 	varchar(255),
	email 		varchar(50) not null,
	password  varchar(255) not null,
	image 		varchar(255),
	CONSTRAINT pk_user PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE task(
	id 					int(255) auto_increment not null,
	user_id 		int(255) not null,
	name 				varchar(255) not null,
	status 			varchar(50) not null,
	created_at  datetime DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT pk_task PRIMARY KEY(id),
	CONSTRAINT fk_task_user FOREIGN KEY(user_id) REFERENCES user(id)
)ENGINE=InnoDb;
