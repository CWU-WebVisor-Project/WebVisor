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
	
	$class1_id = extract_int($_GET, 'class1_id');
	$class1_info = get_class_info($class1_id);
	$class2_id = extract_int($_GET, 'class2_id');
	$class2_info = get_class_info($class2_id);
	$term = extract_int($_GET, 'term');
	
	$conflicts = get_class_conflicts($class1_id, $class2_id, $term);
	
	$term_text = term_text($term);
	$term_text = $term_text['term']. " ".$term_text['year'];
	
?>
</head>
<body>

<?php
	echo(messages());
	echo(linkmenu());
?>
<div><a href='class.php?id=<?php echo($class_id); ?>'>Return to Class</a></div>

	<h1><?php echo($class1_info['name']); ?> &amp; <?php echo($class2_info['name']); ?>&mdash;<?php echo($term_text); ?></h1>
	
	<h2>Conflicted Students</h2>
	
<?php
	foreach ($conflicts as $id => $student_info)
	{
		echo("\t\t".$student_info['last'].", ".$student_info['first']." (".$student_info['cwu_id'].")<br />\n");
	}
?>	

</body>
</html>