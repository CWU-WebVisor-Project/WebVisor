<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel='stylesheet' type='text/css' href='_style.css' />
<?php
	
	include_once("_html.php");
	include_once("_sql.php");
				
	$user_info = get_user_info();
	$user_id = $user_info['id'];
	$superuser = is_superuser($user_info);
	
	if (!$user_info || !$superuser)
	{
		echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}
	
	$class_id = extract_int($_GET, 'id', extract_int($_POST, 'id', 0));
	
	if (isset($_POST['add_class']))
	{
		$name = $_POST['new_name'];
		$title = $_POST['new_title'];
		$credits = $_POST['new_credits'];
		$fall = extract_yesno($_POST, 'new_fall');
		$winter = extract_yesno($_POST, 'new_winter');
		$spring = extract_yesno($_POST, 'new_spring');
		$summer = extract_yesno($_POST, 'new_summer');
		
		$class_id = add_class($user_id, $name, $credits, $title, $fall, $winter, $spring, $summer);
	}
	
	if (isset($_POST['update_class']))
	{
		$name = $_POST['update_name'];
		$title = $_POST['update_title'];
		$credits = $_POST['update_credits'];
		$fall = extract_yesno($_POST, 'update_fall');
		$winter = extract_yesno($_POST, 'update_winter');
		$spring = extract_yesno($_POST, 'update_spring');
		$summer = extract_yesno($_POST, 'update_summer');
		$active = extract_yesno($_POST, 'update_active');
		
		update_class($user_id, $class_id, $name, $title, $credits, $fall, $winter, $spring, $summer, $active);
		
		$prereq_ids = array();
		// all the new prerequisites
		foreach ($_POST as $key => $value)
		{
			if ($key == 'update_prereqs')
			{
				$prereq_ids[] = $value;
			}
			
			$required_grades = extract_id_values('grade', $_POST);
		}
			
		update_prereqs($user_id, $class_id, $prereq_ids, $required_grades);
	}
	
	$all_classes = all_classes();
	$all_classes_blank = array('0' => '') + $all_classes;
	$all_credits = all_credits();

	if ($class_id != 0)
	{
		$class_info = get_class_info($class_id);
		$name = $class_info['name'];
		$title = $class_info['title'];
		$credits = $class_info['credits'];
		$fall = $class_info['fall'];
		$winter = $class_info['winter'];
		$spring = $class_info['spring'];
		$summer = $class_info['summer'];
		$rosters = get_class_rosters($class_id);
		$prereqs = get_prereqs($class_id);
		$all_grades = all_grades();
	}

?>
	<title>Class<?php if ($name != '') echo(" - $name"); ?></title>
</head>
<body>

<?php
	echo(messages());
	echo(linkmenu());
?>
<h1>Class Information<?php if ($name != '') { echo(" &mdash; $name"); } ?></h1>

<form action='class.php' method='post'>

	<table class='input'>
		<tr>
			<td>Class:</td>
			<td>
<?php echo(array_menu("\t\t\t\t", $all_classes_blank, 'id', $class_id, true)); ?>
			</td>
<?php
	if ($class_id == 0)
	{
?>
			<td class='spacer' />
			<td>New Catalog Name:</td>
			<td><input type='textarea' class='nameid' name='new_name' value='' /> (e.g., MATH 153)</td>
<?php
	}
?>
		</tr>
<?php
	if ($class_id == 0)
	{
?>
		<tr>
			<td class='spacer' />
			<td class='spacer' />
			<td class='spacer' />
			<td>Name:</td>
			<td><input type='textarea' class='nameid' name='new_title' value='' /> (e.g., Precalculus)</td>
		</tr>
		<tr>
			<td class='spacer' />
			<td class='spacer' />
			<td class='spacer' />
			<td>Credits:</td>
			<td><?php echo(array_menu("\t\t\t", $all_credits, 'new_credits', '4')); ?></td>
		</tr>
		<tr>
			<td class='spacer' />
			<td class='spacer' />
			<td class='spacer' />
			<td>Offered:</td>
			<td>
				<label class='checkbox'>Fall <?php echo(checkbox("\t\t\t", 'new_fall', false)); ?></label>
				<label class='checkbox'>Winter <?php echo(checkbox("\t\t\t", 'new_winter', false)); ?></label>
				<label class='checkbox'>Spring <?php echo(checkbox("\t\t\t", 'new_spring', false)); ?></label>
				<label class='checkbox'>Summer <?php echo(checkbox("\t\t\t", 'new_summer', false)); ?></label>
			</td>
		</tr>
		<tr>
			<td class='spacer' />
			<td class='spacer' />
			<td class='spacer' />
			<td />
			<td><input type='submit' name='add_class' value='Add Class Info' /></td>
		</tr>
<?php
	}
?>
	</table>
<?php
	if ($class_id != 0)
	{
?>

	<h2>Class Information</h2>
	
	<table class='input'>
		<tr>
			<td>Catalog Designation:</td>
			<td><input type='textarea' class='nameid' name='update_name' value='<?php echo($name); ?>' /></td>
		</tr>
		<tr>
			<td>Name:</td>
			<td><input type='textarea' class='nameid' name='update_title' value='<?php echo($title); ?>' /></td>
		</tr>
		<tr>
			<td>Credits:</td>
			<td>
<?php echo(array_menu("\t\t\t\t", $all_credits, 'update_credits', "$credits")); ?>
			</td>
		</tr>
		<tr>
			<td>Offered:</td>
			<td>
				<label class='checkbox'>Fall <?php echo(checkbox("", 'update_fall', ($fall==$YES))); ?></label>
				<label class='checkbox'>Winter <?php echo(checkbox("", 'update_winter', ($winter==$YES))); ?></label>
				<label class='checkbox'>Spring <?php echo(checkbox("", 'update_spring', ($spring==$YES))); ?></label>
				<label class='checkbox'>Summer <?php echo(checkbox("", 'update_summer', ($summer==$YES))); ?></label>
				
			</td>
		</tr>
		<tr>
			<td colspan='2'>Prerequisites:</td>
		</tr>
<?php
	
		foreach($prereqs as $id => $info)
		{
			$name = $info['name'];
			$min = $info['minimum_grade'];
?>
		<tr>
			<td />
			<td><?php echo($name); ?> with a
<?php echo(array_menu("\t\t\t\t", $all_grades, "grade-$id", $min)); ?>
			</td>
		</tr>
<?php
		}	
?>
		<tr>
			<td colspan='2'>Add Prerequisites:</td>
		</tr>
		<tr>
			<td />
			<td>
				<select multiple='multiple' name='update_prereqs'>
<?php
		foreach ($all_classes as $id => $name)
		{
			if (!array_key_exists($id, $prereqs))
			{
?>
					<option value='<?php echo($id); ?>'><?php echo($name); ?></option>
<?php
			}
		}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Active:</td>
			<td><input type='checkbox' name='update_active' <?php if ($class_info['active'] == $YES) { echo("checked='checked'"); } ?>>
		<tr>
			<td />
			<td><input type='submit' name='update_class' value='Update Class Info' /></td>
		</tr>
	</table>

	<h2>Expected Enrollment</h2>

	<table class='schedule'>
<?php
	foreach ($rosters as $catalog_year => $term)
	{
		$next_year = $catalog_year + 1;
?>
		<tr class='header'>
			<td>Fall <?php echo($catalog_year); ?></td>
			<td>Winter <?php echo($next_year); ?></td>
			<td>Spring <?php echo($next_year); ?></td>
			<td>Summer <?php echo($next_year); ?></td>
		</tr>
		<tr>
<?php
		for($term_number = 1; $term_number < 5; ++$term_number)
		{
?>
<?php
			if (count($term[$term_number]) > 0)
			{
?>
		<td class='enrolled'>
			<a href='roster.php?class_id=<?php echo("$class_id&amp;term=$catalog_year$term_number"); ?>'><?php echo(count($term[$term_number])); ?></a>
		</td>
<?php
			}
			else
			{
?>
		<td class='empty'>
					<?php echo(count($term[$term_number])); ?>
		</td>
<?php
			}
?>
<?php
		}
?>
	</tr>
<?php
	}
?>
	</table>

</form>

<?php
	}
?>

</body>
</html>