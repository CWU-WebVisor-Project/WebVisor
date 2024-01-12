<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel='stylesheet' type='text/css' href='_style.css' />
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
	
	$template_id = extract_int($_GET, 'id', extract_int($_POST, 'template_id', 0));
	
	if ($template_id == 0)
	{
		$program_id = extract_int($_GET, 'program_id', 0);
		if ($program_id != 0)
		{
			$template_id = create_template($program_id);
		}
	}
	
	if ($template_id == 0)
	{
		echo("<meta http-equiv='refresh' content='0; url=program.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}
	
	if (isset($_POST['update_template']))
	{
		$name = extract_string($_POST, 'update_name', '');
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/tempqtr-([0-9]*)/", $key, $matches))
			{
				$class_id = $matches[1];
				if (isset($template[$class_id]))
				{
					$template[$class_id]["qtr"] = $value;
				}
				else
				{
					$template[$class_id] = array("qtr" => $value);
				}
			}
			if (preg_match("/tempyr-([0-9]*)/", $key, $matches))
			{
				$class_id = $matches[1];
				if (isset($template[$class_id]))
				{
					if ($value == 0)
					{
						unset($template[$class_id]);
					}
					else
					{
						$template[$class_id]["year"] = $value;
					}
				}
				else
				{
					$template[$class_id] = array("year" => $value);
				}
			}
		}
		update_template($template_id, $name, $template);
	}
		
	if ($template_id != 0)
	{
		$template_info = get_template_info($template_id);
		$program_id = $template_info['program_id'];
		$template_name = $template_info['name'];
		
		$program_info = get_program_info($program_id);
		$major_id = $program_info['major_id'];
		$major_name = $program_info['name'];
		$program_year = $program_info['year'];
		
		$program_classes = get_program_classes($program_id);
		$template_classes = get_template_classes($template_id);
		//! @todo should order program classes by their template position, not their names
	}

?>
	<title>Program<?php if ($program_id != 0) echo(" - $major_name ($program_year)"); ?></title>
</head>
<body>


<?php echo(messages()); ?>

<?php echo(linkmenu()); ?>

<div><a href='program.php?program_id=<?php echo($program_id); ?>'>Return To Program</a></div>

<h1 id='top'>Template Information<?php if ($program_id != 0) { echo(" &mdash; $major_name ($program_year)"); } ?></h1>

<form action='template.php' method='post'>
	
	<input type='hidden' name='template_id' value="<?php echo($template_id); ?>"/>

	<table class='input'>
		<tr>
			<td>Name:</td>
			<td colspan='4'>
				<input type="text" name='update_name' value="<?php echo($template_name); ?>"/>
			</td>
		</tr>
		<tr>
			<td colspan='5'>Program Classes:</td>
		</tr>
		<tr>
			<td />
			<td>Name</td>
			<td>Quarter</td>
			<td>Year</td>
			<td />
		</tr>
<?php
	
		$row_num = 1;
		foreach($program_classes as $id => $info)
		{
			$tempyr = $template_classes[$id]['year'];
			$tempqtr = $template_classes[$id]['quarter'];
			$name = $info['name_credits'];
			$class='';
			if (($row_num % 2) == 1)
			{
				$class = "class='alt'";
			}
			$row_num++;
?>
		<tr>
			<td />
			<td <?php echo($class); ?>><?php echo($name); ?></td>
			<td <?php echo($class); ?> align='center'>
<?php echo(array_menu("\t\t\t\t", array('1' => 'Fall', '2' => 'Winter', '3' => 'Spring', '4' => 'Summer'), "tempqtr-$id", $tempqtr)); ?>
			</td>
			<td <?php echo($class); ?> align='center'>
<?php echo(array_menu("\t\t\t\t", array('0' => 'None', '1' => '1', '2' => '2', '3' => '3', '4' => '4'), "tempyr-$id", $tempyr)); ?>
			</td>
			<td />
		</tr>
<?php
		}	
?>
		<tr>
			<td />
			<td colspan='4'><input type='submit' name='update_template' value='Update Template' /></td>
		</tr>
	</table>	

</form>

</body>
</html>