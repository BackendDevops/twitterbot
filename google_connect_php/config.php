<?php

/*

create database google_connect_php;
use google_connect_php

create table users (
	id int not null auto_increment primary key,
	google_user_id varchar(30) unique,
	google_email varchar(255),
	google_name varchar(255),
	google_picture varchar(255),
	google_access_token varchar(255),
	created datetime,
	modified datetime 
);

*/

define('DSN','mysql:host=localhost;dbname=google_connect_php');
define('DB_USER','root');
define('DB_PASSWORD','mangoshake');

define('CLIENT_ID','523168918850-4v6v0v78ocggm0u2rvd7ljpdoe664f2u.apps.googleusercontent.com');
define('CLIENT_SECRET','hXr_fp3Ny6QkYl7CxdHt1DLj');

define('SITE_URL','http://192.168.33.10/google_connect_php/');

error_reporting(E_ALL & ~E_NOTICE);

session_set_cookie_params(0,'/google_connect_php/');