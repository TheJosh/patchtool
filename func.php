<?php
require_once 'config.php';


mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);

mysql_select_db(DATABASE_NAME);


function db_query($q) {
	$res = mysql_query($q);
	
	if ($res === false) {
		die("Query error\n");
	}
	
	return $res;
}

function db_quote($str) {
	return mysql_real_escape_string($str);
}

function html($str) {
	return htmlspecialchars($str);
}

function format_datetime($mysql_date) {
	$ts = strtotime($mysql_date);
	$diff = time() - $ts;
	
	if ($diff < 60) {
		return '1 min ago';
		
	} else if ($diff < 60 * 60) {
		return ceil($diff / 60) . ' mins ago';
		
	} else if ($diff < 60 * 60 * 24) {
		return ceil($diff / 60 / 60) . ' hours ago';
		
	} else if ($diff < 60 * 60 * 24 * 5) {
		return ceil($diff / 60 / 60 / 24) . ' days ago';
		
	} else {
		return date('d/m/Y', $ts);
	}
}

