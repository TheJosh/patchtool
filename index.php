<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>LibreOffice Patch Viewer</title>
	<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="head">
	<h1>LibreOffice Patch Viewer</h1>
</div>
<div id="body">
	
	
	<?php
	$q = "SELECT patches.id, patches.name, patches.pushed, patches.added, patches.updated,
			COUNT(attachments.id) AS num_attachments
		FROM patches
		INNER JOIN messages ON messages.patch_id = patches.id
		INNER JOIN attachments ON attachments.message_id = messages.id AND attachments.name != 'not available'
		GROUP BY patches.id
		ORDER BY patches.updated DESC, patches.id DESC
		LIMIT 1000";
	$res = db_query($q);
	
	echo '<table>';
	echo '<tr><th colspan="2">Name</th><th>Num Files</th><th>Added</th><th>Updated</th></tr>';
	while ($row = mysql_fetch_assoc($res)) {
		echo '<tr>';
		echo '<td><a href="patch.php?id=', $row['id'], '">', html($row['name']), '</a></td>';
		echo '<td>';
			if ($row['pushed']) echo '<span class="info-label">Pushed</span>';
		echo '</td>';
		echo '<td>', html($row['num_attachments']), '</td>';
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