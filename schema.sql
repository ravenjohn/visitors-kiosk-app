drop database bigas2hack_db;
create database bigas2hack_db;
use bigas2hack_db;

CREATE TABLE users(
	id int(11) auto_increment primary key,
	access_token varchar(40),
	name varchar(32) not null,
	password varchar(32),
	type enum('visitor','admin','superadmin') default 'visitor' not null,
	affiliation varchar(128),
	country varchar(64),
	category varchar(32),
	contact varchar(128),
	date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP not null,
	date_updated TIMESTAMP null
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE logs(
	id int(11) auto_increment primary key,
	uri varchar(255) NOT NULL,
	method varchar(6) NOT NULL,
	params text,
	access_token varchar(40) NOT NULL,
	user_id varchar(40) NOT NULL,
	ip_address varchar(45) NOT NULL,
	authorized tinyint(1) NOT NULL,
	date_created int(11) NOT NULL,
	date_updated int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
