<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	
	//! @todo we should also look for bad student id numbers
	
	include_once("_html.php");
	include_once("_sql.php");
	
	if (!get_user_info())
	{
		echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}
	
	$lost_students = get_lost_students();
	$bad_cwu_ids = get_bad_cwu_ids();
	
?>
	<link rel="stylesheet" type="text/css" href="_style.css">
</head>
<body>

<?php
	echo(messages());
	echo(linkmenu());
?>

	<h2>Misinformed Students</h2>

	<p>The table below students whose plans have enrolled them in classes during terms when those classes are not typically offered.</p>
	<table>
		<tr><td>Term</td><td>Class</td><td>Student Name</td></tr>
<?php
	foreach ($lost_students as $info)
	{
		$term_text = term_text($info['term']);
		$term_text = $term_text['term'].' '.$term_text['year'];
		$class_name = $info['class_name'];
		$class_id = $info['class_id'];
		$student_name = $info['student_name'];
		$cwu_id = $info['cwu_id'];
		echo("\t\t<tr><td style='padding:0px 5px;'>$term_text</td><td style='padding:0px,10px;'><a href='class.php?id=$class_id'>$class_name</a><td><a href='student.php?id=$cwu_id'>$student_name</a></td></tr>\n");
	}
?>
	</table>
	
	<h2>Bad CWU IDs</h2>

	<p>The table below show students whose CWU ID number is not valid.</p>

	<table>
		<tr><td>CWU ID</td><td>Name</td><td>Email</td><td>Active</td></tr>
<?php
	foreach ($bad_cwu_ids as $info)
	{
		$name = $info['name'];
		$cwu_id = $info['cwu_id'];
		$email = $info['email'];
		$active = $info['active'];
		echo("\t\t<tr><td style='padding:0px 5px;'>$cwu_id</td><td style='padding:0px 5px;'><a href='student.php?id=$cwu_id'>$name</a></td><td style='padding:0px 5px;'>$email</td><td style='padding:0px 5px;'>$active</td></tr>\n");
	}
?>
</body>
</html>