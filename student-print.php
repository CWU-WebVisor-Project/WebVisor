<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	
	include_once("_common.php");
	include_once("_html.php");
	include_once("_sql.php");
	
	$user_info = get_user_info();
	
	if (!$user_info)
	{
		echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
		echo("</head>\n");
		echo("</html>\n");
		die();
	}
	
	$user_name = $user_info['name'];
	
	$student_id = cwu_id_to_student_id(extract_int($_GET, 'cwu_id', 0));
	$program_id = extract_int($_GET, 'program_id', 0);

	if ($student_id == 0 || $program_id == 0)
	{
?>
	<meta http-equiv='refresh' content='0; url=http://webwork.math.cwu.edu/webvisor/student.php' />
<?php
	}

	$student_info = get_student_info($student_id);
	$cwu_id = $student_info['cwu_id'];
	$email = $student_info['email'];
	$name = $student_info['name'];
	$first = $student_info['first'];
	$last = $student_info['last'];
	$active = $student_info['active'];
	$phone = $student_info['phone'];
	$address = $student_info['address'];

	$postbaccalaureate = $student_info['postbaccalaureate'];
	$second_major = $student_info['second_major'];
	$withdrawing = $student_info['withdrawing'];
	$veterans_benefits = $student_info['veterans_benefits'];
	
	$start_year = $end_year = date('Y');
	if (date('m') < 7)
	{
		--$start_year;
	}
	else
	{
		++$end_year;
	}
	$start_year = extract_int($_POST, 'start_year', $start_year);
	$end_year = max($start_year + 1, extract_int($_POST, 'end_year', $end_year));

	$plan = get_plan($student_id, $start_year, $end_year);
	$classes = $plan['by term'];
	ksort($classes);
	$class_ids = $plan['by id'];
	
	$notes = get_notes($student_id);
	
//! @todo need to update this for the program
//	$student_program_info = get_student_program_info($student_id, $program_id);
//	$advisor = $student_program_info['advisor'];
//	$catalog_year = $student_program_info['catalog_year'];
//	$graduation_year = $student_program_info['graduation_year'];

	$electives_credits = get_electives_credits($student_id, $program_id);
	$electives = $electives_credits['electives'];
	$elective_credits = $electives_credits['credits'];
	
	$program_info = get_program_info($program_id);
	$program_name = $program_info['name'];
	$program_elective_credits = $program_info['elective_credits'];
	
	$required_classes = get_required_classes($program_id);
	$replacement_classes = get_replacement_classes($program_id);
?>
	<title>Plan for Graduation</title>
	<link rel='stylesheet' type='text/css' href='_style.css' />
	<style>
	</style>

<?php
?>
</head>
<body class='print'>
	
<div class='firstpage'>

<h1>Plan for Graduation - <?php echo($program_name); ?></h1>

<table>
	<tr>
		<td width='25%'><span style='font-weight:bold;'>Name:</span></td>
		<td width='25%'><span style='font-weight:bold;'>CWU ID:</span></td>
		<td width='25%'><span style='font-weight:bold;'>Email:</span></td>
		<td width='25%'><span style='font-weight:bold;'>Phone:</span></td>
	</tr>
	<tr>
		<td width='25%' class='tt'><?php echo($name); ?></td>
		<td width='25%' class='tt'><?php echo($cwu_id); ?></td>
		<td width='25%' class='tt'><?php echo($email); ?>@cwu.edu</td>
		<td width='25%' class='tt'><?php echo($phone); ?></td>
	</tr>
	<tr>
		<td width='30%'><pre><?php echo($address); ?></pre></td>
	</tr>
</table>

<div class='noprint' style='width:100%; text-align:center; margin-bottom:10px;'><a href='student.php?id=<?php echo($cwu_id); ?>'>Online Version</a></div>

<h2><?php echo($program_name); ?> Core Courses</h2>

<table class='requirements' width='100%'>
	<tr>
<?php

		$row = 0;
		$col = 0;
		foreach($required_classes as $required_id => $info)
		{
			$required_name = $info['name'];
			$checkbox = ( array_key_exists($required_id, $class_ids) ? "&#9635;" : "&#9634;" );
?>
		<td width='20%'><?php echo("$checkbox $required_name"); ?></td>
<?php
	
			++$col;
			if ($col == 5)
			{
				++$row;
				$col = 0;
?>
	</tr>
	<tr>
<?php
			}
		}
?>
	</tr>
</table>

<h2>Electives (<?php echo($program_elective_credits); ?> credits required)</h2>

<table width=100%>
<?php
	echo("<tr>\n");
	$col = 0;
	foreach ($electives as $id => $class_info)
	{
		$class_name = $class_info['name'];
		$class_term = $class_info['term'];
		$year = substr($class_term, 0, 2);
		$term = substr($class_term, 2, 1);
		echo("<td width=25%>&#9635; <span class='tt'>$class_name</span></td>\n");
		++$col;
		if ($col % 4 == 0) {
			echo"</tr><tr>\n";
		}
	}
	for($col = $col; $col < 4; ++$col)
	{
		echo("<td width=25%></td>");
	}
	echo("</tr>\n");
?>
</table>

<table class='signatures'>
		<tr>
			<td width=10% style='text-align:right;'>Student:</td>
			<td width=30% style='border-bottom: 2px solid black;'>&nbsp;</td>
			<td width=10% style='text-align:right;'>Advisor:</td>
			<td width=30% style='border-bottom: 2px solid black;'>&nbsp;</td>
			<td width=10% style='text-align:right;'>Date:</td>
			<td width=10% style='border-bottom: 2px solid black;'>&nbsp;</td>
		</tr>
		<tr>
			<td />
			<td class='tt'><?php echo($name); ?></td>
			<td />
			<td class='tt'><?php echo($user_name); ?></td>
		</tr>
</table>

</div>

<div class='page'>

<h2>Plan</h2>

<div style='width:100%; font-weight:bold; margin-top:30px;'>Please see your advisor before deviating from your plan. Some courses are offered as infrequently as every other year and course scheduling is guided by plans on file with the department.</div>

<table width='100%'>

<?php
	foreach ($classes as $catalog_year => $terms)
	{
		if ($catalog_year == 0)
		{
			continue;
		}
		
		$next_year = $catalog_year + 1;
		$qtrly_classes = array("", "", "", "", "");
		$qtrly_credits = array(0,0,0,0,0);
		for($term_number = 1; $term_number < 5; ++$term_number)
		{
			$term_classes = $terms[$term_number];
			$slots = count($term_classes);
			for ($j = 0; $j < $slots; ++$j)
			{
				$class_info = get_class_info($term_classes[$j]['class_id'], $program_id);
				$class_name = $class_info['name']." (".$class_info['credits']." cr)";
				if ($class_info['minimum_grade'] > 7)
				{
					$class_name .= " @ ".points_to_grade($class_info['minimum_grade']);
				}
				$qtrly_classes[$term_number] .= "$class_name\n";
				$qtrly_credits[$term_number] += $class_info['credits'];
			}
		}
?>
	<tr class='header'>
		<td>Fall <?php echo("$catalog_year (".$qtrly_credits[1]." cr)"); ?></td>
		<td>Winter <?php echo("$next_year (".$qtrly_credits[2]." cr)"); ?></td>
		<td>Spring <?php echo("$next_year (".$qtrly_credits[3]." cr)"); ?></td>
		<td>Summer <?php echo("$next_year (".$qtrly_credits[4]." cr)"); ?></td>
	</tr>
	<tr>
<?php
		for($term_number = 1; $term_number < 5; ++$term_number)
		{
?>
		<td valign='top' width=25% class='tt' >
<?php echo($qtrly_classes[$term_number]); ?>
		</td>
<?php
		}
?>
	</tr>
<?php
	}
?>
</table>

<h2>Notes</h2>

<dl>
<?php
	foreach ($notes as $id => $info)
	{
		if ($info['flagged'] == $YES)
		{
?>
	<dt><?php echo($info['tag']); ?></dt>
	<dd><?php echo($info['note']); ?></dd>
<?php			
		}
	}
?>
</dl>

<table>
		<tr>
			<td width=10% style='text-align:right;'>Student:</td>
			<td width=30% style='border-bottom: 2px solid black;'>&nbsp;</td>
			<td width=10% style='text-align:right;'>Advisor:</td>
			<td width=30% style='border-bottom: 2px solid black;'>&nbsp;</td>
			<td width=10% style='text-align:right;'>Date:</td>
			<td width=10% style='border-bottom: 2px solid black;'>&nbsp;</td>
		</tr>
		<tr>
			<td />
			<td class='tt'><?php echo($name); ?></td>
			<td />
			<td class='tt'><?php echo($user_name); ?></td>
		</tr>
</table>

</div>

</body>
</html>