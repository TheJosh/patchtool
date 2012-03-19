<?php
require_once '../func.php';

$queries = array(
	"DROP TABLE IF EXISTS messages",
	"DROP TABLE IF EXISTS attachments",
	"DROP TABLE IF EXISTS patches",
	
	"CREATE TABLE messages (
		id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		thread_id INT UNSIGNED NOT NULL,
		main_index VARCHAR(20),
		fromaddr VARCHAR(255),
		subject VARCHAR(255),
		body MEDIUMTEXT,
		added DATETIME,
		UNIQUE INDEX (main_index)
	)",
	
	"CREATE TABLE attachments (
		id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		message_id INT UNSIGNED NOT NULL,
		name VARCHAR(255),
		url VARCHAR(255)
	)",
	
	"CREATE TABLE threads (
		id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
		name VARCHAR(255),
		added DATETIME,
		updated DATETIME,
		pushed TINYINT UNSIGNED NOT NULL,
		UNIQUE INDEX (name)
	)",
);

foreach ($queries as $q) {
	db_query($q);
}
