<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	
	include_once("_html.php");
	include_once("_sql.php");
	
	if (!get_user_info())
	{
		echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}
	
	$class_id = extract_int($_GET, 'class_id');
	$class_info = get_class_info($class_id);
	$term = extract_int($_GET, 'term');
	
	$roster = get_class_roster($class_id, $term);
	
	$term_text = term_text($term);
	$term_text = $term_text['term']. " ".$term_text['year'];
	
	$intersections = get_class_intersections($class_id, $term);
	
?>
</head>
<body>

<?php
	echo(messages());
	echo(linkmenu());
?>
<div><a href='class.php?id=<?php echo($class_id); ?>'>Return to Class</a></div>

	<h1><?php echo($class_info['name']); ?>&mdash;<?php echo($term_text); ?></h1>
	
	<h2>Conflicting Classes</h2>
	
<?php
	foreach ($intersections as $id => $intersect_info)
	{
		$class2_id = $intersect_info['id'];
		echo("\t\t<a href='conflict.php?term=$term&amp;class1_id=$class_id&amp;class2_id=$class2_id'>".$intersect_info['name']." (".$intersect_info['count'].")</a><br />\n");
	}
?>	

	<h2>Roster</h2>
	
	<table>
		<tr><th>Name</th><th>CWU ID</th><th>Email</th></tr>
<?php
	foreach ($roster as $id => $student_info)
	{
		$cwu_id = $student_info['cwu_id'];
		$name = $student_info['name'];
		$email = $student_info['email'];
		echo("\t\t<tr><td style='padding:0px 10px;'><a href='student.php?id=$cwu_id'>$name</a></td><td style='padding:0px 10px;'>$cwu_id</td><td style='padding:0px 10px;'><a href='mailto:$email@cwu.edu'>$email@cwu.edu</a></td></tr>\n");
	}
?>
	</table>
</body>
</html>