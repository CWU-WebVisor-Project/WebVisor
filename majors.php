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
	
	$program_id = extract_int($_GET, 'program_id');

	if ($program_id != 0)
	{
		$roster = get_program_roster($program_id);
	}
	
	$all_programs_blank = array(0 => '') + all_programs();
?>
</head>
<body>

<?php
	echo(messages());
	echo(linkmenu());
?>

	<form action='majors.php' method='get'>
<?php echo(array_menu("\t\t", $all_programs_blank, 'program_id', $program_id, false)); ?>
		<br />
		<input type='submit' /> 
	</form>
	<table>
		<tr><th>Name</th><th>CWU ID</th><th>Email</th><th>Advisor</th></tr>
<?php
	foreach ($roster as $id => $student_info)
	{
		$cwu_id = $student_info['cwu_id'];
		$name = $student_info['name'];
		$email = $student_info['email'];
		$advisor = $student_info['advisor'];
		echo("\t\t<tr><td style='padding:0px 10px;'><a href='student.php?id=$cwu_id'>$name</a></td><td style='padding:0px 10px;'>$cwu_id</td><td style='padding:0px 10px;'><a href='mailto:$email@cwu.edu'>$email@cwu.edu</a></td><td>$advisor</td></tr>\n");
	}
?>
	</table>
</body>
</html>
