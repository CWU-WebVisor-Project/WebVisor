<?php
	
	$SECRETARY_NAME = "Brenda Bland";
	$SECRETARY_EMAIL = "Brenda.Bland";

	// DEPENDS ON PROGRAM!!!
	$ADVISOR_NAME = "";
	$ADVISOR_EMAIL = "";
	$ADVISOR_OFFICE = "";

	$PROGRAMMER_EMAIL = "Aaron.Montgomery";
	
	$CHAIR_NAME = "Janet Shiver";
		
	$START_HOUR = 80;
	$END_HOUR = 170;
	
//--------  --------  --------  --------  --------  --------  --------  --------  --------  --------
// FORM FUNCTIONS	
	
	function get_post($key)
	{
		return isset($_POST[$key]) ? $_POST[$key] : '';
	}
	
//--------  --------  --------  --------  --------  --------  --------  --------  --------  --------
// CALENDAR FUNCTIONS
	
	function get_events($start_date, $end_date)
	{
		$events = array();
		
		$start_date_string = $start_date->format('Y-m-d');
		$end_date_string = $end_date->format('Y-m-d');
		$query="
			SELECT
				CONCAT(hour,'-',date) AS hash,
				status
			FROM
				Student_Appointments
			WHERE
				date BETWEEN '$start_date_string' AND '$end_date_string'
				AND
				(
					status='Scheduled'
					OR
					status='Requested'
					OR
					status='Walk In'
				)
			ORDER BY
				hour, date ASC;";
		$result = my_query($query);
		
		$rows = mysql_num_rows($result);
		for ($i = 0; $i < $rows; ++$i)
		{
			$row = mysql_fetch_array($result);
			$hash = $row['hash'];
			if ($events[$hash] != 'Scheduled')
			{
				$events[$hash] = $row['status'];
			}
		}
		
		return $events;
	}

//--------  --------  --------  --------  --------  --------  --------  --------  --------  --------
// FORMATTING FUNCTIONS

	function advisor_email()
	{
		global $ADVISOR_EMAIL;
		global $ADVISOR_NAME;
		
		return "<a href='mailto:$ADVISOR_EMAIL'>$ADVISOR_NAME</a>";
	}

	function pretty_time($hour)
	{
		if ($hour > 125)
		{
			$hour = $hour - 120;
		}
		
		if ($hour % 10 == 0)
		{
			$minutes = "00";
		}
		else
		{
			$minutes = "30";
			$hour -= 1;
		}
		$hour = round($hour/10);
		
		return "$hour:$minutes";
	}
	
	function pretty_date($date)
	{
		return $date->format('D M d');
	}
	
	function pretty_date_time($date, $time)
	{
		return pretty_date($date)." @ ".pretty_time($time);
	}
	
	function sort_date_times($lhs, $rhs)
	{
		$lhs_date = $lhs[0];
		$lhs_time = $lhs[1];
		
		$rhs_date = $rhs[0];
		$rhs_time = $rhs[1];
		
		if ($lhs_date < $rhs_date)
		{
			return -1;
		}
		else if ($lhs_date > $rhs_date)
		{
			return 1;
		}
		else
		{
			if ($lhs_time < $rhs_time)
			{
				return -1;
			}
			else if ($lhs_time > $rhs_time)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
	}
	
	function pretty_date_times($date_times)
	{
		$result = "";
		
		usort($date_times, 'sort_date_times');
		$pretty_date_times = array();		
		foreach($date_times as $date_time)
		{
			$date = $date_time[0];
			$time = $date_time[1];
			
			if (!isset($dated_times[$date]))
			{
				$dated_times[$date] = array(pretty_time($time));
			}
			else
			{
				$dated_times[$date][] = pretty_time($time);
			}
		}

		foreach($dated_times as $date => $times)
		{
			$result .= pretty_date($date);
			$result .= "@ ";
			$result .= implode(', ', $times);
			$result .= "\n";
		}
		
		return $result;
	}

	function hash_to_date_hour($hash)
	{
		$matches = array();
		preg_match('/([0-9]*)-([0-9]*-[0-9]*-[0-9]*)/', $hash, $matches);
		return array_slice($matches, 1);
	}
	
//--------  --------  --------  --------  --------  --------  --------  --------  --------  --------
// MAIL FUNCTIONS

	$SEND_MAIL = true;
	$PRINT_MAIL = false;
	
	function my_mail($email, $subject, $message, $name, $id)
	{
		global $SEND_MAIL;
		global $PRINT_MAIL;
		
		if ($email == '' || $id == 1)
		{
			return;
		}
		
		if ($PRINT_MAIL)
		{
			echo("=============================<br />\n");
			echo("TO: &lt;$email&gt;<br />\n");
			echo("SUBJECT: &lt;$subject&gt;<br />\n");
			echo("\n$message<br />\n");
			echo("=============================<br />\n");
		}
		
		if ($SEND_MAIL)
		{
			global $ADVISOR_EMAIL;
			global $PROGRAMMER_EMAIL;
		
			mail("$email@cwu.edu, $ADVISOR_EMAIL@cwu.edu, $PROGRAMMER_EMAIL@cwu.edu", "[CS Advising] $subject", $message, "From:$ADVISOR_EMAIL@cwu.edu");
		}
	}

?>