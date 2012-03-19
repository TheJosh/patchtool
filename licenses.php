<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>LibreOffice Mailinglist Tool</title>
	<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="head">
	<h1>LibreOffice License Statement Viewer</h1>
</div>
<div id="body">
	
	<div id="nav">
		<a href="patches.php">Patches</a>
		&nbsp;
		<a href="review.php">Review</a>
		&nbsp;
		<a href="licenses.php" class="on">License Statements (coming soon)</a>
	</div>
	
	
	<?php
	$q = "SELECT threads.id, threads.name, threads.added, threads.updated
		FROM threads
		INNER JOIN thread_tags AS t1 ON t1.thread_id = threads.id AND t1.tag = 'license'
		INNER JOIN messages ON messages.thread_id = threads.id
		INNER JOIN attachments ON attachments.message_id = messages.id
		LEFT JOIN thread_tags AS t2 ON t2.thread_id = threads.id AND t2.tag = 'push'
		GROUP BY threads.id
		ORDER BY threads.updated DESC, threads.id DESC
		LIMIT 1000";
	$res = db_query($q);
	
	echo '<table>';
	echo '<tr><th>Name</th><th>Added</th><th>Updated</th></tr>';
	while ($row = mysql_fetch_assoc($res)) {
		echo '<tr>';
		echo '<td><a href="thread.php?id=', $row['id'], '">', html($row['name']), '</a></td>';
		echo '<td>', format_datetime($row['added']), '</td>';
		echo '<td>', format_datetime($row['updated']), '</td>';
		echo '</tr>';
	}
	echo '</table>';
	?>
	
	
</div>
<div id="foot">
	<p>Generated by LibreOffice Patch Viewer v0.1</p>
</div>
</body>
</html>