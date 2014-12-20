<?php

function connectDb(){
	$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	);
	try {
		return new PDO(DSN, DB_USER, DB_PASSWORD, $options);
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit;
	}
}

function h($s){
	return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}