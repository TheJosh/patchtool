<?php
require_once '../func.php';
header('Content-type: text/plain');


$date = date('Y-F');
$url = "http://lists.freedesktop.org/archives/libreoffice/{$date}/date.html";

$html = file_get_contents($url);
if (! $html) die("Can't load html.");

preg_match_all('!<a href="([0-9]+).html">([^<>"]+)</a>!i', $html, $matches, PREG_SET_ORDER);


echo "--- STEP 1; Grabbing messages ---\n";

foreach ($matches as $m) {
	$m[2] = html_entity_decode($m[2]);
	$m[2] = trim($m[2]);
	
	if (! preg_match('!PATCH|PUSHED|REVIEW!' ,$m[2])) continue;
	
	echo "Message: {$m[1]} {$m[2]}\n";
	
	$m[1] = db_quote($m[1]);
	$m[2] = db_quote($m[2]);
	
	$q = "INSERT IGNORE INTO messages SET
		main_index = '{$m[1]}',
		subject = '{$m[2]}',
		thread_id = 0,
		body = ''";
	db_query($q);
	
	flush();
}


echo "\n\n--- STEP 2; Grabbing bodies and attachments ---\n";
$q = "SELECT id, thread_id, main_index FROM messages WHERE body = '' ORDER BY id ASC";
$res = db_query($q);

while ($msg = mysql_fetch_assoc($res)) {
	$url = "http://lists.freedesktop.org/archives/libreoffice/{$date}/{$msg['main_index']}.html";
	
	echo "Load body: {$msg['id']} {$msg['thread_id']} {$msg['main_index']}\n";
	
	$html = file_get_contents($url);
	if (! $html) die("Can't load html.");
	
	preg_match('!<i>(.+?)</i>!ims', $html, $matches);
	$added = date('Y-m-d H:i:s', strtotime(trim(html_entity_decode($matches[1]))));
	
	preg_match('!<b>(.+?)</b>!ims', $html, $matches);
	$fromaddr = trim(html_entity_decode($matches[1]));
	
	preg_match('!<pre>(.+?)</pre>!ims', $html, $matches);
	$body = trim(html_entity_decode($matches[1]));
	
	$parts = explode('-------------- next part --------------', $body);
	
	echo "Number of message parts: ", count($parts), "\n";
	
	$body = db_quote(strip_tags(array_shift($parts)));
	$added = db_quote($added);
	$fromaddr = db_quote($fromaddr);
	$q = "UPDATE messages SET body = '{$body}', fromaddr = '{$fromaddr}', added = '{$added}' WHERE id = {$msg['id']}";
	db_query($q);
	
	foreach ($parts as $p) {
		if (! preg_match('!^Name: (.+)$!m', $p, $matches)) continue;
		$name = $matches[1];
		if ($name == 'not available') continue;
		
		if (! preg_match('!<a href="([^"]+)"!i', $p, $matches)) continue;
		$url = $matches[1];
		
		echo "Got attachment: {$name}\n";
		
		$name = db_quote($name);
		$url = db_quote($url);
		$q = "INSERT INTO attachments SET message_id = {$msg['id']}, name = '{$name}', url = '{$url}'";
		db_query($q);
	}
	
	flush();
}


echo "\n\n--- STEP 3; Matchup messages to threads ---\n";
$q = "SELECT id, subject, added, body FROM messages WHERE thread_id = 0 ORDER BY id ASC";
$res = db_query($q);

while ($msg = mysql_fetch_assoc($res)) {
	$subj = $msg['subject'];
	$subj = preg_replace('!\[[^\]]+\]!', '', $subj);
	$subj = preg_replace('!(re|fwd):!i', '', $subj);
	$subj = preg_replace('!\s+!', ' ', $subj);
	$subj = trim($subj);
	
	$subj_sql = db_quote($subj);
	$date_sql = db_quote($msg['added']);
	
	$q = "SELECT id FROM threads WHERE name LIKE '{$subj_sql}' LIMIT 1";
	$thread_res = db_query($q);
	
	if (mysql_num_rows($thread_res) == 0) {
		echo "Creating new thread: {$subj}\n";
		
		$q = "INSERT INTO threads SET name = '{$subj_sql}', added = '{$date_sql}', updated = NOW()";
		db_query($q);
		$thread_id = mysql_insert_id();
		
	} else {
		$row = mysql_fetch_assoc($thread_res);
		$thread_id = $row['id'];
	}
	
	echo "Linking to thread: {$thread_id}\n";
	
	$q = "UPDATE messages SET thread_id = {$thread_id} WHERE id = {$msg['id']}";
	db_query($q);
	
	$q = "UPDATE threads SET updated = '{$date_sql}' WHERE id = {$thread_id}";
	db_query($q);
	
	flush();
}



echo "\n\n--- STEP 4; Tag threads ---\n";

$q = "TRUNCATE TABLE thread_tags";
db_query($q);

$q = "SELECT thread_id FROM messages WHERE subject LIKE '%patch%'";
$res = db_query($q);
while ($msg = mysql_fetch_assoc($res)) {
	$q = "INSERT IGNORE INTO thread_tags SET thread_id = {$msg['thread_id']}, tag = 'patch'";
	db_query($q);
	echo "Attached 'patch' to thread # {$msg['thread_id']}\n";
}


$q = "SELECT thread_id FROM messages WHERE subject LIKE '%pushed%' OR body LIKE '%pushed%'";
$res = db_query($q);
while ($msg = mysql_fetch_assoc($res)) {
	$q = "INSERT IGNORE INTO thread_tags SET thread_id = {$msg['thread_id']}, tag = 'push'";
	db_query($q);
	echo "Attached 'push' to thread # {$msg['thread_id']}\n";
}


$q = "SELECT thread_id FROM messages WHERE subject LIKE '%review%'";
$res = db_query($q);
while ($msg = mysql_fetch_assoc($res)) {
	$q = "INSERT IGNORE INTO thread_tags SET thread_id = {$msg['thread_id']}, tag = 'review'";
	db_query($q);
	echo "Attached 'review' to thread # {$msg['thread_id']}\n";
}


