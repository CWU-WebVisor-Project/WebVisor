<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	
	include_once("_html.php");
	include_once("_sql.php");
	
	$user_info = get_user_info();

	if (!$user_info || !is_superuser($user_info))
	{
		echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}
	
	$user_id = $user_info['id'];
		
	//! @todo should be able to restrict by user, student, class, program, major
	$journal = get_journal();
?>
	<title>Journal</title>
	<link rel='stylesheet' type='text/css' href='_style.css' />
</head>
<body>

<?php echo(messages()); ?>
<?php echo(linkmenu()); ?>

<h1>Journal</h1>

	<table>	
		<tr>
			<th>Date</td>
			<th>User</td>
			<th>Student</td>
			<th>Class</td>
			<th>Program</td>
			<th>Major</td>
			<th>Note</td>
		</tr>
<?php
	foreach($journal as $entry)
	{
?>
		<tr>
			<td><?php echo($entry['date']); ?></td>
			<td><?php echo($entry['user_name']); ?></td>
			<td><?php echo($entry['student_name']); ?></td>
			<td><?php echo($entry['class_name']); ?></td>
			<td><?php echo($entry['program_name']); ?></td>
			<td><?php echo($entry['major_name']); ?></td>
			<td><?php echo(htmlentities($entry['note'])); ?></td>
		</tr>
<?php
	}
?>
	</table>

</body>
</html>