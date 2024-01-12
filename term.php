<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	
	include_once("_html.php");
	include_once("_sql.php");
	
	//! @todo maybe allow anonymous access here since there is no updating
	if (!get_user_info())
	{
		echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}

	$curr_year = date('Y');
	$year = extract_int($_GET, 'year', $curr_year);

	//! @todo should go back to previous year if we are in Winter or Spring
	for ($i = $curr_year - 1; $i < $curr_year+10; ++$i)
	{
		$years[$i] = "$i";
	}
	
	$enrollments = get_enrollments($year);
?>
	<title>Enrollments</title>
	<link rel='stylesheet' type='text/css' href='_style.css' />
</head>
<body>

<?php echo(messages()); ?>
<?php echo(linkmenu()); ?>

<h1>Enrollments</h1>

<form action='' method='get'>
Year Starting Fall:
<?php
	echo(array_menu('', $years, 'year', $year, true));
?>
</form>

<table>
	<tr>
		<td>Class Name</td>
		<td style='padding:0px 10px;'>Fall <?php echo($year); ?></td>
		<td style='padding:0px 10px;'>Winter <?php echo($year+1); ?></td>
		<td style='padding:0px 10px;'>Spring <?php echo($year+1); ?></td>
		<td style='padding:0px 10px;'>Summer <?php echo($year+1); ?></td>
	</tr>
<?php
	foreach($enrollments as $class_id => $info)
	{
		$name = $info['name'];
		$enrollment = $info['enrollment'];
		if ($class == 'even')
		{
			$class = 'odd';
		}
		else
		{
			$class = 'even';
		}
?>
	<tr class='<?php echo($class); ?>'>
		<td><a href='class.php?id=<?php echo($class_id); ?>'><?php echo($name); ?></a></td>
<?php
		for ($i = 1; $i < 5; ++$i)
		{
?>
		<td style='text-align:center;'><a href='roster.php?class_id=<?php echo($class_id); ?>&amp;term=<?php echo($year.$i); ?>'><?php echo($enrollment[$i]); ?></a></td>
<?php
		}
?>
	</tr>
<?php
	}
?>

</table>

</body>
</html>