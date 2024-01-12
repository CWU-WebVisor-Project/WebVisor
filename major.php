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
	
	$major_id = extract_int($_GET, 'major_id', extract_int($_POST, 'id', 0));
	
	if (isset($_POST['add_major']))
	{
		$new_name = extract_string($_POST, 'new_name');
		
		$major_id = add_major($user_id, $new_name, $YES);
	}
	
	if (isset($_POST['update_major']))
	{		
		$name = $_POST['update_name'];
		$active = $_POST['update_active'];

		update_major($user_id, $major_id, $name, $active);
	}
	
	$all_majors = all_majors();
	$all_majors_blank = array('0' => '') + $all_majors;
	if ($major_id != 0)
	{
		$major_info = get_major_info($major_id);
		$name = $major_info['name'];
		$active = $major_info['active'];
	}

?>

<?php echo(messages()); ?>

	<title>Major<?php if ($name != '') echo(" - $name"); ?></title>
</head>
<body>

<div><?php echo(linkmenu()); ?></div>

<h1>Major Information<?php if ($name != '') { echo(" &mdash; $name"); } ?></h1>

<a href='./majors.php'>List of All Students in Major</a>

<form action='major.php' method='post'>

	<table class='input'>
		<tr>
			<td>Major:</td>
			<td>
<?php echo(array_menu("\t\t\t\t", $all_majors_blank, 'id', $major_id, true)); ?>
			</td>
<?php
	if ($major_id == 0)
	{
?>
			<td class='spacer' />
			<td>New Major Name:</td>
			<td><input type='textarea' class='nameid' name='new_name' value='' />
<?php
	}
?>
		</tr>
<?php
	if ($major_id == 0)
	{
?>
		<tr>
			<td class='spacer' />
			<td class='spacer' />
			<td class='spacer' />
			<td />
			<td><input type='submit' name='add_major' value='Add Major' /></td>
		</tr>
<?php
	}
?>
	</table>
<?php
	if ($major_id != 0)
	{
?>

	<h2>Major Information</h2>
	
	<table class='input'>
		<tr>
			<td>Name:</td>
			<td colspan='7'><input type='textarea' class='nameid' name='update_name' value='<?php echo($name); ?>' /></td>
		</tr>
		<tr>
			<td>Active:</td>
			<td colspan='7'>
<?php echo(array_menu("\t\t\t\t", all_yesno(), 'update_active', $YES, false)); ?></td>
		</tr>
		<tr>
			<td />
			<td><input type='submit' name='update_major' value='Update Major' /></td>
		</tr>
	</table>
	
</form>

<?php
	}
?>

</body>
</html>