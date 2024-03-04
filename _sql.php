<?php
	require_once __DIR__ . '/vendor/autoload.php';
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
//-----------------------------------------------------	
//! DATABASE ACCESS
//-----------------------------------------------------	
	$link = null;
	function add_sql_message($message)
	{
		global $PRINT_SQL;
		if ($PRINT_SQL)
		{
			add_message("<div class='sql'>$message</div>");		
		}
		else
		{
			echo("<!-- $message -->");
		}
	}
	
	//! @todo https://stackoverflow.com/questions/1581610/how-can-i-store-my-users-passwords-safely
function get_user_info($login = '', $password = '', $setCookies = true)
{
	// Use environment variables for configuration
	$host = $_ENV['DB_HOST'];
	$port = $_ENV['DB_PORT'];
	$user = $_ENV['DB_USER'];
	$pass = $_ENV['DB_PASS'];
	$dbName = $_ENV['DB_NAME'];
	global $link;
	// Establish connection to the database
	$link = mysqli_connect($host, $user, $pass, $dbName, $port);
	if (!$link) {
		add_message("Error: Could not link to server, please contact Aaron.<br />");
		return false;
	}

	// Retrieve login details from cookies if not provided
	if (empty($login) && empty($password) && isset($_COOKIE['webvisor_login']) && isset($_COOKIE['webvisor_password'])) {
		$login = $_COOKIE['webvisor_login'];
		$password = $_COOKIE['webvisor_password'];
		// Note: Storing passwords, even hashed, in cookies is highly insecure and not recommended.
	}

	// Prepare statement to prevent SQL injection
	$stmt = $link->prepare("SELECT * FROM Users WHERE login = ?");
	$stmt->bind_param("s", $login);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows == 0) {
		return false;
	} else {
		$user = $result->fetch_assoc();
		// Verify the hashed password
		if (password_verify($password, $user['password'])) {
			if ($setCookies) {
				$oneYear = time() + 60 * 60 * 24 * 365;
				setcookie('webvisor_login', $login, $oneYear);
				setcookie('webvisor_password', $password, $oneYear);
				// Do not store passwords or hashed passwords in cookies
			}
			return $user;
		} else {
			return false;
		}
	}
}

	function add_user($id, $login, $password, $name, $program_id, $superuser, $last, $first) {

		global $link;
		// Assume $mysqli is your MySQLi connection instance from the previous example.

		// Sanitize input (basic example, consider more thorough cleaning depending on context)
		$login = filter_var($login, FILTER_SANITIZE_STRING);
		$name = filter_var($name, FILTER_SANITIZE_STRING);
		$last = filter_var($last, FILTER_SANITIZE_STRING);
		$first = filter_var($first, FILTER_SANITIZE_STRING);
		// For 'id', 'program_id', and 'superuser', ensure they are integers or booleans as expected.
		// Password should be hashed for security reasons, not just sanitized.

		// Hash the password
		$passwordHash = password_hash($password, PASSWORD_DEFAULT);

		// Convert 'superuser' to a more standard boolean/integer representation if needed
		$superuserValue = $superuser == "Yes" ? "Yes" : "No";

		// Prepare the SQL statement
		$stmt = $link->prepare("INSERT INTO users (id, login, password, name, program_id, superuser, last, first) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

		// Bind parameters to the prepared statement
		// 'i' denotes integer, 's' denotes string
		$stmt->bind_param("isssisss", $id, $login, $passwordHash, $name, $program_id, $superuserValue, $last, $first);

		// Execute the prepared statement
		if ($stmt->execute()) {
			echo "New user added successfully";
		} else {
			echo "Error: " . $stmt->error;
		}
		// Close statement and connection
		$stmt->close();
	}

function is_superuser($user_info)
{
	global $YES;
	// Check if $user_info is an array and has the 'superuser' key
	if (is_array($user_info) && isset($user_info['superuser'])) {
		return ($user_info['superuser'] == $YES);
	}
	// Return false or a suitable default if $user_info is not an array or doesn't have 'superuser'
	return false;
}
	function update_user($user_id, $password, $name, $program_id) {
		global $link; // Assuming $link is your mysqli connection object

		// Initialize the base query
		$query_string = "UPDATE Users SET password=?, name=?";
		$types = "ss"; // Types for password and name
		$params = [$password, $name]; // Parameters array

		// Check if program_id is valid and should be included in the update
		if (isset($program_id) && $program_id > 0) {
			$query_string .= ", program_id=?";
			$types .= "i"; // Adding integer type for program_id
			$params[] = $program_id; // Adding program_id to parameters array
		}

		$query_string .= " WHERE id=?";
		$types .= "i"; // Adding integer type for user_id
		$params[] = $user_id; // Adding user_id to parameters array

		// Prepare the statement
		$stmt = mysqli_prepare($link, $query_string);
		if (!$stmt) {
			echo "Prepare failed: (" . mysqli_errno($link) . ") " . mysqli_error($link);
			return false;
		}

		// Dynamically bind parameters
		mysqli_stmt_bind_param($stmt, $types, ...$params);

		// Execute the query
		if (!mysqli_stmt_execute($stmt)) {
			echo "Execute failed: (" . mysqli_stmt_errno($stmt) . ") " . mysqli_stmt_error($stmt);
			return false;
		}

		// Close the statement
		mysqli_stmt_close($stmt);

		return true; // Indicate success
	}


function all_users()
	{
		$query_string = "
		SELECT
			*
		FROM
			Users
		ORDER BY
			name ASC
		;";
		$query_result = my_query($query_string, false);

		$users = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$user_id = $row['id'];
			$name = $row['name'];
			$users[$user_id] = $name;
		}

		return $users;
	}

	$PRINT_SQL = false;
	$EXECUTE_SQL = true;

	function print_sql()
	{
		global $PRINT_SQL;
		$PRINT_SQL = true;
	}

	function noprint_sql()
	{
		global $PRINT_SQL;
		$PRINT_SQL = false;
	}

	function execute_sql()
	{
		global $EXECUTE_SQL;
		$EXECUTE_SQL = true;
	}

	function noexecute_sql()
	{
		global $EXECUTE_SQL;
		$EXECUTE_SQL = false;
	}

	function my_query($query_string, $print)
	{
		global $PRINT_SQL;
		global $EXECUTE_SQL;
		global $link;

		if ($print || $PRINT_SQL)
		{
			add_sql_message("$query_string");
		}

		$result = false;
		if ($EXECUTE_SQL)
		{
			$result = mysqli_query($link, $query_string);
		}

		if (!$result && ($print || $PRINT_SQL))
		{
			$err_no = mysqli_errno($link);
			$err_str = mysqli_error($link);
			add_sql_message("ERROR: $err_no: $err_str");
		}

		return $result;
	}

//-----------------------------------------------------
//! JOURNAL
//-----------------------------------------------------

	function get_journal($cleanup = false, $user_id = 0, $student_id = 0, $class_id = 0, $program_id = 0, $major_id = 0)
	{
		$query_string = "
		SELECT
			Journal.date,
			Users.name AS user_name,
			CONCAT(Students.last, \", \", Students.first) AS student_name,
			Classes.name AS class_name,
			Programs.year AS program_name,
			Majors.name AS major_name,
			note
		FROM 
			Journal
			LEFT JOIN Users ON Journal.user_id=Users.id
			LEFT JOIN Students ON Journal.student_id=Students.id
			LEFT JOIN Classes ON Journal.class_id=Classes.id
			LEFT JOIN Programs ON Journal.program_id=Programs.id
			LEFT JOIN Majors ON Journal.major_id=Majors.id
		ORDER BY
			date DESC
		LIMIT
			100;
		";

		$query_result = my_query($query_string, false);

		$result = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$result[] = $row;
		}
		return $result;
	}

//-----------------------------------------------------
//! MAJORS
//-----------------------------------------------------

	function record_update($user_id, $note)
	{
			$query_string = "
			INSERT INTO
				Journal(user_id, note)
			VALUES
				($user_id, '$note')
			;";
			$query_result = my_query($query_string, false);
	}

	function record_update_major($user_id, $major_id, $note)
	{
			$query_string = "
			INSERT INTO
				Journal(user_id, major_id, note)
			VALUES
				($user_id, '$note', $major_id)
			;";
			$query_result = my_query($query_string, false);
	}

	function record_update_program($user_id, $program_id, $note)
	{
			$query_string = "
			INSERT INTO
				Journal(user_id, program_id, note)
			VALUES
				($user_id, $program_id, '$note')
			;";
			$query_result = my_query($query_string, false);
	}

	function record_update_class($user_id, $class_id, $note)
	{
			$query_string = "
			INSERT INTO
				Journal(user_id, class_id, note)
			VALUES
				($user_id, $class_id, '$note')
			;";
			$query_result = my_query($query_string, false);
	}

	function record_update_student($user_id, $student_id, $note)
	{
			$query_string = "
			INSERT INTO
				Journal(user_id, student_id, note)
			VALUES
				($user_id, $student_id, '$note')
			;";
			$query_result = my_query($query_string, false);
	}

//-----------------------------------------------------
//! MAJORS
//-----------------------------------------------------

	function all_majors()
	{
		$query_string = "
		SELECT
			id, name, active
		FROM
			Majors
		ORDER BY
			name
		;";
		$query_result = my_query($query_string, false);

		$all_majors = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$all_majors[$row['id']] = $row['name'];
		}

		return $all_majors;
	}

	function add_major($user_id, $name, $active)
	{
		global $link;
		$query_string = "
			INSERT INTO
				Majors(name, active)
			VALUES
				('$name', '$active')
			;";
		$query_result = my_query($query_string, false);

		$major_id = mysqli_insert_id($link);

		if ($major_id > 0)
		{
			$note = "<major:$major_id> added.";
			record_update_major($user_id, $major_id, $note);
		}

		return $major_id;
	}

	function update_major($user_id, $major_id, $name, $active)
	{
		global $link;
		$query_string = "
			UPDATE
				Majors
			SET
				name='$name',
				active='$active'
			WHERE
				id=$major_id
			;";
		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "<major:$major_id> updated.";
			record_update_major($user_id, $major_id, $note);
		}
	}

	function get_major_info($major_id)
	{
		$query_string = "
		SELECT
			name, active
		FROM
			Majors
		WHERE id=$major_id
		;";
		$query_result = my_query($query_string, false);

		$row = mysqli_fetch_assoc($query_result);
		return $row;
	}

//-----------------------------------------------------
//! PROGRAMS
//-----------------------------------------------------

	function get_program_id($major_id, $catalog_year)
	{
		//! @bug should use $catalog_year and do a search based on first year program was offered
		$catalog_year = 2017;

		$query_string = "
		SELECT
			id
		FROM
			Programs
		WHERE
			major_id=$major_id
			AND
			year=$catalog_year
		;";
		$query_result = my_query($query_string, false);

		$row = mysqli_fetch_assoc($query_result);
		return $row['id'];
	}

	function get_program_name($program_id)
	{
		$query_string = "
		SELECT
			name
		FROM
			Majors JOIN Programs ON Majors.id=Programs.major_id
		WHERE
			Programs.id=$program_id
		;";
		$query_result = my_query($query_string, false);
		$row = mysqli_fetch_assoc($query_result);
		$name = $row['name'];

		$query_string = "
		SELECT 
		";
	}

//! @todo program credits should be calculated from class credits + elective credits

	function all_programs($user_id = 0)
	{
		global $link;
		$query_string = "
		SELECT
			Programs.id,
			name,
			year
		FROM
			Programs JOIN Majors ON Programs.major_id=Majors.id
		WHERE
			name != ''
		ORDER BY
			name
		;";
		$query_result = my_query($query_string, false);

		$all_programs = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$all_programs[$row['id']] = $row['name']." (".$row['year'].")";
		}

		if ($user_id != 0)
		{
			$query_string = "
			SELECT
				program_id
			FROM
				User_Programs
			WHERE
				user_id=$user_id
			ORDER BY
				sequence DESC
			;";
			$query_result = mysqli_query($link, $query_string);

			//$favorite_programs = array(-1 => '-');
			$favorite_programs = array();
			while ($row = mysqli_fetch_array($query_result))
			{
				$favorite_programs = array($row['program_id'] => $all_programs[$row['program_id']]);
				//$favorite_programs = array($row['program_id'] => $all_programs[$row['program_id']]) + $favorite_programs;
			}

			$all_programs = $favorite_programs + $all_programs;

		}

		return $all_programs;
	}

	function get_program_info($program_id)
	{
		$query_string = "
		SELECT
			Programs.id, Programs.major_id, Majors.name, Programs.year, Programs.credits, Programs.elective_credits, Programs.active
		FROM
			Majors JOIN Programs ON Majors.id=Programs.major_id
		WHERE
			Programs.id=$program_id
		;";
		$query_result = my_query($query_string, false);

		$row = mysqli_fetch_assoc($query_result);
		return $row;
	}

	function get_program_roster($program_id)
	{
		$query_string = "
		SELECT
			Students.last,
			Students.first,
			CONCAT(Students.last, \", \", Students.first) AS name,
			Students.cwu_id,
			Students.email,
			Users.name AS advisor
		FROM
			Students
			JOIN Student_Programs ON Students.id=Student_Programs.student_id
			JOIN Users ON Student_Programs.user_id=Users.id
		WHERE
			Student_Programs.program_id=$program_id
			AND
			Students.active='Yes'
		ORDER BY
			Students.last, Students.first ASC
		";
		$query_result = my_query($query_string, false);

		$result = array();
		while (null != ($row = mysqli_fetch_assoc($query_result)))
		{
			$result[] = $row;
		}

		return $result;
	}

	function add_program($user_id, $major_id, $year, $template_id)
	{
		global $link;
		$program_id = 0;

		if ($template_id == 0)
		{
			$query_string = "
			INSERT INTO
				Programs(major_id, year)
			VALUES
				($major_id, $year)
			;";

			$query_result = my_query($query_string, false);

			$program_id = mysqli_insert_id($link);

		}
		else
		{
			$query_string = "
			INSERT INTO
				Programs(major_id, year, credits, elective_credits)
			SELECT
				$major_id, $year, credits, elective_credits
			FROM
				Programs
			WHERE
				id=$template_id
			;";
			$query_result = my_query($query_string, false);

			$program_id = mysqli_insert_id($link);

			$query_string = "
			INSERT INTO
				Checklists(program_id, sequence, name)		
			SELECT
				$program_id, sequence, name
			FROM
				Checklists
			WHERE
				program_id=$template_id
			;";
			$query_result = my_query($query_string, false);

			$query_string = "
			INSERT INTO
				Program_Classes(program_id, class_id, minimum_grade, sequence_no, template_qtr, template_year, required)
			SELECT
				$program_id, class_id, minimum_grade, sequence_no, template_qtr, template_year, required
			FROM
				Program_Classes
			WHERE
				program_id=$template_id
			;";
			$query_result = my_query($query_string, false);

			$query_string = "
			INSERT INTO
				Replacement_Classes(program_id, required_id, replacement_id)
			SELECT
				$program_id, required_id, replacement_id
			FROM
				Replacement_Classes
			WHERE
				program_id=$template_id
			;";
			$query_result = my_query($query_string, false);
		}

		if ($program_id > 0)
		{
			$note = "Added <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}

		return $program_id;
	}

	function update_program($user_id, $program_id, $major_id, $year, $credits, $elective_credits, $active)
	{
		global $link;
		$query_string = "
		UPDATE
			Programs
		SET
			major_id=$major_id,
			year=$year,
			credits=$credits,
			elective_credits=$elective_credits,
			active='$active'
		WHERE
			id=$program_id
		;";
		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Updated <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}

		//! @todo update electives!

	}

	function update_program_classes($user_id, $program_id, $core_ids, $required_ids, $required_grades, $sequence_numbers)
	{
		global $YES;
		global $NO;
		global $link;

		$changes = 0;

		$query_string = "
		DELETE FROM
			Program_Classes
		WHERE
			program_id=$program_id
		;";
		$query_result = my_query($query_string, false);

		foreach($core_ids as $class_id)
		{
			$query_string = "
			INSERT INTO Program_Classes
				(program_id, class_id, required)
			VALUES
				($program_id, $class_id, '$NO')
			;";
			$query_result = my_query($query_string, false);

			$changes += mysqli_affected_rows($link);
		}

		foreach ($required_ids as $required_id)
		{
			$query_string = "
			UPDATE Program_Classes
			SET required='$YES'
			WHERE
				program_id=$program_id
				AND
				class_id=$required_id
			;";
			$query_result = my_query($query_string, false);
			$changes += mysqli_affected_rows($link);
		}

		foreach ($required_grades as $class_id => $minimum_grade)
		{
			if ($minimum_grade > 0)
			{
				$query_string = "
				UPDATE Program_Classes
				SET
					minimum_grade=$minimum_grade
				WHERE
					program_id=$program_id
					AND
					class_id=$class_id
				;";

				$query_result = my_query($query_string, false);
				$changes += mysqli_affected_rows($link);
			}
		}

		foreach ($sequence_numbers as $class_id => $seqno)
		{
			$query_string = "
			UPDATE Program_Classes
			SET
				sequence_no=$seqno
			WHERE
				program_id=$program_id
				AND
				class_id=$class_id
			;";

			$query_result = my_query($query_string, false);
			$changes += mysqli_affected_rows($link);
		}

		if ($changes > 0)
		{
			$note = "Updated <program:$program_id> classes.";
			record_update_program($user_id, $program_id, $note);
		}
	}

	// returns all classes required for program with given id
	function get_required_classes($program_id)
	{
		global $YES;

		$required_classes = array();
		$query_string = "
		SELECT
			Classes.id,
			CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name_credits,
			Classes.name,
			Program_Classes.minimum_grade,
			Program_Classes.sequence_no
		FROM
			Classes JOIN Program_Classes ON Program_Classes.class_id=Classes.id
		WHERE
			Program_Classes.program_id = $program_id
			AND
			Program_Classes.required = '$YES'
		ORDER BY
			Classes.name ASC
		;";
		$result = my_query($query_string, false);

		$num_rows = mysqli_num_rows($result);
		for ($i = 0; $i < $num_rows; ++$i)
		{
			$row = mysqli_fetch_assoc($result);
			$id = $row['id'];
			$required_classes[$id] = $row;
		}

		return $required_classes;
	}

	function get_program_classes($program_id)
	{
		$program_classes = array();
		$query_string = "
		SELECT
			Classes.id,
			CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name_credits,
			Classes.name,
			Program_Classes.minimum_grade,
			Program_Classes.sequence_no,
			Program_Classes.required
		FROM
			Classes JOIN Program_Classes ON Program_Classes.class_id=Classes.id
		WHERE
			Program_Classes.program_id = $program_id
		ORDER BY
			sequence_no, name ASC
		;";
		$result = my_query($query_string, false);

		$num_rows = mysqli_num_rows($result);
		for ($i = 0; $i < $num_rows; ++$i)
		{
			$row = mysqli_fetch_assoc($result);
			$id = $row['id'];
			$program_classes[$id] = $row;
		}

		return $program_classes;
	}

	function add_replacement($user_id, $program_id, $replaced_id, $replacement_id)
	{
		global $link;
		$query_string = "
		INSERT INTO
			Replacement_Classes(program_id, required_id, replacement_id)
		VALUES
			($program_id, $replaced_id, $replacement_id)
		;";
		my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Added <replacement:$replacement_id> as replacement for <replaced:$replaced_id> in <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}
	}

	function remove_replacement($user_id, $program_id, $replaced_id, $replacement_id)
	{
		global $link, $user_id;
		$query_string = "
		DELETE FROM Replacement_Classes
		WHERE
			program_id = $program_id
			AND
			required_id = $replaced_id
			AND
			replacement_id = $replacement_id
		;";
		my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Removed <replacement:$replacement_id> as replacement for <replaced:$replaced_id> in <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}
	}

	function get_replacement_classes($program_id)
	{
		$replacement_classes = array();
		$query_string = "
		SELECT
			Replacement_Classes.required_id,
			Replacement_Classes.replacement_id,
			Req.name AS required_name,
			Rep.name AS replacement_name,
			Replacement_Classes.note AS note
		FROM
			Replacement_Classes JOIN Classes AS Rep ON Replacement_Classes.replacement_id=Rep.id JOIN Classes AS Req ON Replacement_Classes.required_id = Req.id
		WHERE
			Replacement_Classes.program_id=$program_id
		;";
		$result = my_query($query_string, false);

		$num_rows = mysqli_num_rows($result);
		for ($i = 0; $i < $num_rows; ++$i)
		{
			$row = mysqli_fetch_assoc($result);
			$required_id = $row['required_id'];
			$required_name = $row['required_name'];
			$replacement_id = $row['replacement_id'];
			$replacement_name = $row['replacement_name'];
			$note = $row['note'];
			if (!isset($replacement_classes[$required_id]))
			{
				$replacement_classes[$required_id] = array('name' => $required_name);
				$replacement_classes[$required_id]['replacements'] = array();
			}
			$replacement_classes[$required_id]['replacements'][] = array('id' => $replacement_id, 'name' => $replacement_name, 'note' => $note);
		}

		return $replacement_classes;
	}

	function get_checklist($program_id)
	{
		$checklist = array();

		$query_string = "
		SELECT
			id, name, sequence
		FROM
			Checklists
		WHERE
			program_id=$program_id
		ORDER BY
			sequence ASC
		;";
		$query_result = my_query($query_string, false);

		while ($row = mysqli_fetch_assoc($query_result))
		{
			$checklist[$row['id']] = $row;
		}

		return $checklist;
	}

	function update_checklist_sequence($user_id, $program_id, $checklist_items)
	{
		global $link;
		$changes = 0;

		asort($checklist_items);
		$max_checklist_count = 1000;

		$query_string = "
			UPDATE
				Checklists
			SET
				sequence=sequence+$max_checklist_count
			WHERE
				program_id=$program_id
			;";
		$query_result = my_query($query_string, false);
		$changes += mysqli_affected_rows($link);

		$i = 1;
		foreach ($checklist_items as $id => $sequence)
		{
			if ($sequence > 0)
			{
				$query_string = "
				UPDATE
					Checklists
				SET
					sequence = $i
				WHERE
					id=$id
				;";
				$query_result = my_query($query_string, false);
				$changes += mysqli_affected_rows($link);
				++$i;
			}
		}

		$query_string = "
			DELETE FROM
				Checklists
			WHERE
				sequence > $max_checklist_count
			;";
			$query_result = my_query($query_string, false);
			$changes += mysqli_affected_rows($link);

		if ($changes > 0)
		{
			$checklist_id = null;
			$note = "Updated <checklist:$checklist_id> for <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}
	}

	function add_checklist_item($user_id, $program_id, $name)
	{
		global $link;
		$query_string = "
			SELECT
				COUNT(id) AS count
			FROM
				Checklists
			WHERE
				program_id=$program_id
			;";
		$query_result = my_query($query_string, false);
		$row = mysqli_fetch_assoc($query_result);
		$count = $row['count'];
		$sequence = $count + 1;

		$query_string = "
			INSERT INTO
				Checklists(program_id, name, sequence)
			VALUES
				($program_id, '$name', $sequence)
			;";
		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$checklist_id = null;
			$note = "Added item to <checklist:$checklist_id> for <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}
	}

//-----------------------------------------------------
//! TEMPLATE
//-----------------------------------------------------

	function get_templates($program_id)
	{
		$query_string = "
		SELECT
			id, name
		FROM
			Templates
		WHERE
			program_id=$program_id
		;";
		$query_result = my_query($query_string, false);

		$templates = array();
		$num_rows = mysqli_num_rows($query_result);
		for ($i = 0; $i < $num_rows; ++$i)
		{
			$row = mysqli_fetch_array($query_result);
			$id = $row['id'];
			$name = $row['name'];
			$templates[$id] = $name;
		}

		return $templates;
	}

	function get_named_templates($program_id)
	{
		$query_string = "
		SELECT
			id, name
		FROM
			Templates
		WHERE
			program_id=$program_id
			AND
			name != '** New **'
		;";
		$query_result = my_query($query_string, false);

		$templates = array();
		$num_rows = mysqli_num_rows($query_result);
		for ($i = 0; $i < $num_rows; ++$i)
		{
			$row = mysqli_fetch_array($query_result);
			$id = $row['id'];
			$name = $row['name'];
			$templates[$id] = $name;
		}

		return $templates;
	}

	function create_template($user_id, $program_id, $name, $mimic_id)
	{
		global $link;
		$query_string = "
			INSERT INTO
				Templates(program_id, name)
			VALUES
				($program_id, '$name')
			;";
		$query_result = my_query($query_string, false);

		$template_id = mysqli_insert_id($link);

		if ($mimic_id != 0)
		{
			$query_string = "
				INSERT INTO
					Template_Classes(template_id, class_id, quarter, year)
				SELECT
					$template_id, class_id, quarter, year
				FROM
					Template_CLasses
				WHERE
					template_id=$mimic_id
				;";
			$query_result = my_query($query_string, false);
		}

		if ($template_id > 0)
		{
			$note = "Created <template:$template_id> for <program:$program_id>.";
			record_update_program($user_id, $program_id, $note);
		}

		return $template_id;
	}

	function get_template_info($template_id)
	{
		$query_string = "
			SELECT
				program_id,
				name
			FROM
				Templates
			WHERE
				id=$template_id
			;";
		$query_result = my_query($query_string, false);
		$row = mysqli_fetch_assoc($query_result);

		return $row;
	}

	function get_template_classes($template_id)
	{
		$query_string = "
			SELECT
				class_id,
				quarter,
				year
			FROM
				Template_Classes
			WHERE
				template_id = $template_id
			;";
		$query_result = my_query($query_string, false);

		$template_classes = array();
		$num_rows = mysqli_num_rows($query_result);
		for ($i = 0; $i < $num_rows; ++$i)
		{
			$row = mysqli_fetch_assoc($query_result);
			$class_id = $row['class_id'];
			$template_classes[$class_id] = $row;
		}

		return $template_classes;
	}

	function update_template($template_id, $name, $template)
	{
		//! @bug this may not be working correctly, the DELETE FROM needs to be checked
		$query_string = "
			UPDATE
				Templates
			SET
				name='$name'
			WHERE
				id=$template_id
			;";
		$query_result = my_query($query_string, false);

		$query_string = "
			DELETE FROM
				Template_Classes
			WHERE
				template_id = $template_id
			;";
		$query_result = my_query($query_string, false);

		foreach ($template as $class_id => $qtr_year)
		{
			$qtr = $qtr_year["qtr"];
			$year = $qtr_year["year"];
			$query_string = "
				INSERT INTO
					Template_Classes(template_id, class_id, quarter, year)
				VALUES
					($template_id, $class_id, $qtr, $year)
				ON DUPLICATE KEY UPDATE
					quarter=$qtr,
					year=$year
				;";
			$query_result = my_query($query_string, false);
		}
	}

//-----------------------------------------------------
//! CLASS
//-----------------------------------------------------

/*
	A class has the following properties

	- name (e.g., MATH 153)
	- title (e.g., Precalculus I)
	- credits (e.g., 5)
	- active (e.g., Yes)
	- fall (e.g., Yes)
	- winter (e.g., Yes)
	- spring (e.g., Yes)
	- summer (e.g., Yes)

	these are passed around as a "class_info" array

	*/

	// list of all classes in an array of the form id => name
	// primarily useful for creating menus of all classes
	// if $program_id is not 0, we list required classes first
	// and provide information about minimum grades
	function all_classes($program_id = 0)
	{
		$all_classes = array();

		if ($program_id != 0)
		{
			// $program_id != 0
			$query_string = "
			SELECT
				Classes.id,
				CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name,
				Program_Classes.minimum_grade,
				COALESCE(Program_Classes.sequence_no, 1000) AS seqno
			FROM
				Classes LEFT JOIN Program_Classes ON Classes.id=Program_Classes.class_id
			WHERE
				Program_Classes.program_id=$program_id
			ORDER BY
				active, seqno, name ASC";

			$query_result = my_query($query_string, false);
			while ($row = mysqli_fetch_assoc($query_result))
			{
				$id = $row['id'];
				$name = $row['name'];
				if (isset($row['minimum_grade']) && $row['minimum_grade'] > 7)
				{
					$name .= " @ ".points_to_grade($row['minimum_grade']);
				}
				$all_classes[$id] = $name;
			}

			$query_string = "
			SELECT
				Classes.id,
				CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name
			FROM
				Classes
			ORDER BY
				active,
				name ASC
				;";

			$query_result = my_query($query_string, false);
			while ($row = mysqli_fetch_assoc($query_result))
			{
				$id = $row['id'];
				if (!array_key_exists($id, $all_classes))
				{
					$name = $row['name'];
					if (isset($row['minimum_grade']) && $row['minimum_grade'] > 7)
					{
						$name .= " @ ".points_to_grade($row['minimum_grade']);
					}
					$all_classes[$id] = $name;
				}
			}
		}
		else
		{
			// $program_id = 0
			$query_string = "
			SELECT
				Classes.id,
				CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name
			FROM
				Classes
			ORDER BY
				active,
				name ASC
				;";

			$query_result = my_query($query_string, false);
			while ($row = mysqli_fetch_assoc($query_result))
			{
				$id = $row['id'];
				$name = $row['name'];
				if (isset($row['minimum_grade']) && $row['minimum_grade'] > 7)
				{
					$name .= " @ ".points_to_grade($row['minimum_grade']);
				}
				$all_classes[$id] = $name;
			}
		}

		return $all_classes;
	}

	// creates the class and returns the class id of the new class
	function add_class($user_id, $name, $credits, $title='', $fall='$NO', $winter='$NO', $spring='$NO', $summer='$NO')
	{
		global $link;
		$query_string = "
		INSERT INTO Classes
			(name, title, credits, fall, winter, spring, summer)
		VALUES
			('$name', '$title', $credits, '$fall', '$winter', '$spring', '$summer')
		;";
		$result = my_query($query_string, false);

		$class_id = mysqli_insert_id($link);

		if ($class_id > 0)
		{
			$note = "<class:$class_id> added.";
			record_update_class($user_id, $class_id, $note);
		}

		return $class_id;
	}

	function update_class($user_id, $class_id, $name, $title, $credits, $fall, $winter, $spring, $summer, $active)
	{
		global $link;
		$query_string = "
		UPDATE
			Classes
		SET
			name='$name',
			title='$title',
			credits=$credits,
			fall='$fall',
			winter='$winter',
			spring='$spring',
			summer='$summer',
			active='$active'
		WHERE
			id=$class_id
			;";

		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Updated <class:$class_id>.";
			record_update_class($user_id, $class_id, $note);
		}
	}

	function update_prereqs($class_id, $prereq_ids, $required_grades)
	{
		// Delete existing prerequisites
		$query_string = "
		DELETE FROM
			Prerequisites
		WHERE
			class_id=$class_id
		;";
		my_query($query_string, false);

		// Ensure $prereq_ids is an array
		$prereq_ids = (array) $prereq_ids;

		// Consolidate insert operations
		foreach($prereq_ids as $prereq_id) {
			// Check if a grade is specified for this prereq_id
			$minimum_grade = isset($required_grades[$prereq_id]) ? $required_grades[$prereq_id] : 'NULL';

			// Check if class_id and prereq_id exist in the classes table (pseudo-code)
			if (check_class_exists($class_id) && check_class_exists($prereq_id)) {
				// Adjusted query to handle potential NULL minimum_grade
				$query_string = "
				INSERT INTO Prerequisites
					(class_id, prerequisite_id, minimum_grade)
				VALUES
					($class_id, $prereq_id, $minimum_grade)
				ON DUPLICATE KEY UPDATE
					minimum_grade = VALUES(minimum_grade)
				;";
				my_query($query_string, false);
			}
		}
	}

	function check_class_exists($class_id) {
		// Prepare the SQL query to check if the class_id exists in the classes table
		$query_string = "SELECT COUNT(*) FROM classes WHERE id = $class_id";

		// Execute the query
		$query_result = my_query($query_string, false); // Assuming my_query executes the query and returns the result

		// Fetch the result. Assuming my_query returns a mysqli_result object
		$row = mysqli_fetch_array($query_result);

		// Check if the count is greater than 0, indicating the class_id exists
		if ($row[0] > 0) {
			return true; // class_id exists
		} else {
			return false; // class_id does not exist
		}
	}


function get_prereqs($class_id)
	{
		$query_string = "
		SELECT
			Prerequisites.prerequisite_id,
			Classes.name,
			Prerequisites.minimum_grade
		FROM
			Prerequisites
			JOIN Classes ON Prerequisites.prerequisite_id=Classes.id
		WHERE
			Prerequisites.class_id = $class_id
		;";
		$query_result = my_query($query_string, false);

		$prereqs = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$prereqs[$row['prerequisite_id']] = $row;
		}

		return $prereqs;
	}

	// returns the class info of the class
	function get_class_info($id, $program_id=0)
	{
		if ($id == null || !is_numeric($id)) {
			// Handle invalid $id, perhaps return null or throw an exception
			return null;
		}
		$query_string = "
		SELECT
			id, name, title, credits, fall, winter, spring, summer, active
		FROM
			Classes
		WHERE
			Classes.id=$id
			;";
/*
		if ($program_id != 0)
		{
			$query_string = "
			SELECT
				Classes.*,
				Program_Classes.minimum_grade
			FROM
				Classes
				LEFT JOIN Program_Classes ON Program_Classes.class_id=Classes.id
			WHERE
				Classes.id=$id
			;";
		}
*/
		$query_result = my_query($query_string, false);
		return mysqli_fetch_assoc($query_result);
	}

	function get_class_id($name, $program_id=0)
	{
		if ($name == null || is_numeric($name)) {
			// Handle invalid $id, perhaps return null or throw an exception
			return null;
		}
		$in_loc=-1;
		if($name != 'NEW'){
			for($i =0; $i<strlen($name);$i++){
				if($name[$i]=='('){
					$in_loc = $i;
				}
			}
		}
		if($in_loc != -1){
			$name = substr($name,0,$in_loc-1);
		}
		$query_string = "
			SELECT
				id
			FROM
				Classes
			WHERE
				Classes.name=\"$name\"
				;";
		/*
				if ($program_id != 0)
				{
					$query_string = "
					SELECT
						Classes.*,
						Program_Classes.minimum_grade
					FROM
						Classes
						LEFT JOIN Program_Classes ON Program_Classes.class_id=Classes.id
					WHERE
						Classes.id=$id
					;";
				}
		*/
		$query_result = my_query($query_string, false);
		return mysqli_fetch_assoc($query_result);
	}

	// $result['catalog_year']['catalog_term'] = array of students in the course that term
	// e.g., "2016 => (1 = > (Joe Smith, Fred Johnson), 3 => (Jane Doe, Fred Johnson))"
	function get_class_rosters($id)
	{
		$rosters = array();

		$query_string = "
		SELECT
			term,
			student_id
		FROM
			Student_Classes
			JOIN Students ON Students.id=Student_Classes.student_id
		WHERE
			class_id=$id
			AND
			Students.active = 'Yes'
		ORDER BY
			term
			;";
		$result = my_query($query_string, false);
		$term_ids = array();
		while($row = mysqli_fetch_assoc($result))
		{
			$term_id = $row['term'];
			$catalog_year = substr($term_id, 0, 4);
			$catalog_term = substr($term_id, 4, 1);
			if (!isset($rosters[$catalog_year]))
			{
				$rosters[$catalog_year] = array();
			}
			if (isset($rosters[$catalog_year]) && is_array($rosters[$catalog_year]) && isset($rosters[$catalog_year][$catalog_term]) && !is_array($rosters[$catalog_year][$catalog_term]))
			{
				$rosters[$catalog_year][$catalog_term] = array();
			}
			$rosters[$catalog_year][$catalog_term][] = $row['student_id'];
		}

		return $rosters;
	}

	function get_class_roster($class_id, $term)
	{
		$rosters = array();

		$query_string = "
		SELECT
			CONCAT(Students.last, ', ', Students.first) AS name,
			Students.email,
			Students.cwu_id
		FROM
			Student_Classes
			JOIN Students ON Student_Classes.student_id=Students.id
		WHERE
			class_id=$class_id
			AND
			term=$term
			AND Students.active = 'Yes'
		ORDER BY
			Students.last, Students.first ASC
			;";

		$query_result = my_query($query_string, false);
		$roster = array();
		while($row = mysqli_fetch_assoc($query_result))
		{
			$roster[] = $row;
		}
		return $roster;
	}

	function get_class_intersections($class_id, $term)
	{
		global $YES;

		$sql_result = my_query("SELECT DISTINCT
    Classes.id,
    Classes.name,
    Count(*) AS count
FROM
    Student_Classes AS Hub
JOIN
    Student_Classes AS Spoke ON Hub.student_id = Spoke.student_id AND Spoke.term = Hub.term
JOIN
    Classes ON Classes.id = Spoke.class_id
JOIN
    Students ON Students.id = Hub.student_id
WHERE
    Hub.class_id = $class_id
    AND Hub.term = $term
    AND Students.active = '$YES'
    AND Hub.class_id != Spoke.class_id
GROUP BY
    Classes.id, Classes.name;
", false);

		$result = array();
		while ($row = mysqli_fetch_assoc($sql_result))
		{
			$result[$row['id']] = $row;
		}

		return $result;

	}

	function get_class_conflicts($class1_id, $class2_id, $term)
	{
		global $YES;

		$sql_result = my_query("SELECT DISTINCT
			Students.id,
			Students.cwu_id,
			Students.first,
			Students.last
		FROM
			Students,
			Student_Classes AS First,
			Student_Classes AS Second
		WHERE
			First.student_id=Second.student_id
			AND
			First.class_id=$class1_id
			AND
			Second.class_id=$class2_id
			AND
			First.term=$term
			AND
			Second.term=$term
			AND
			Students.active='$YES'
			AND
			Students.id=First.student_id
		ORDER BY last, first ASC;", false);

		$result = array();
		while ($row = mysqli_fetch_assoc($sql_result))
		{
			$result[$row['id']] = $row;
		}

		return $result;
	}

//-----------------------------------------------------
//! STUDENTS
//-----------------------------------------------------

	function user_can_update_student($user_id, $student_id)
	{
		$query_string = "
		SELECT
			id
		FROM
			Student_Programs
		WHERE
			user_id=$user_id
			AND
			student_id=$student_id
		;";
		$query_result = my_query($query_string, false);

		return (mysqli_num_rows($query_result) > 0);
	}

	function programs_with_student($student_id)
	{
		$query_string = "
		SELECT
			Programs.id AS program_id,
			CONCAT(Majors.name, ' (', Programs.year, ')') AS program_name,
			Users.id AS advisor_id,
			Users.name AS advisor_name
		FROM
			Student_Programs
			JOIN Programs ON Student_Programs.program_id=Programs.id
			JOIN Majors ON Majors.id = Programs.major_id
			LEFT JOIN Users ON Student_Programs.user_id=Users.id
		WHERE
			student_id=$student_id
		ORDER BY
			Majors.name,
			Programs.year
		;";
		$query_result = my_query($query_string, false);

		$programs = array();
		while($row = mysqli_fetch_assoc($query_result))
		{
			$programs[$row['program_id']] = $row;
		}

		return $programs;
	}

	function student_in_program($student_id, $program_id)
	{
		$query_string = "
		SELECT
			*
		FROM
			Student_Programs
		WHERE
			student_id=$student_id
			AND
			program-id=$program_id
		;";
		$query_result = my_query($query_string, false);

		return (mysqli_num_rows($query_result) > 0);
	}

	function find_user($cwu_id, $email, $first, $last)
	{
		global $link;
		$id = 0;
		if ($cwu_id != '')
		{
			$query_string = "
			SELECT
				id
			FROM
				Students
			WHERE
				cwu_id='$cwu_id';";
		}
		else
		{
			$query_string = "
			SELECT
				id
			FROM
				Students
			WHERE
				email='$email';";
		}
		$result = my_query($query_string, false);

		if (mysqli_num_rows($result) == 0)
		{
			if ($cwu_id != 0 || $email != '')
			{
				if ($cwu_id == '')
				{
					$cwu_id = 'NULL';
				}
				$query_string = "
				INSERT INTO
					Students(cwu_id, email, first, last)
				VALUES
					($cwu_id, '$email', '$first', '$last');";
				$result = my_query($query_string, false);
				$id = mysqli_insert_id($link);
			}
			else
			{
				add_message("Cannot add new user without both a CWU ID and a CWU email address.");
			}
		}
		else
		{
			$row = mysqli_fetch_assoc($result);
			$id = $row['id'];
		}
		return $id;
	}

	function cwu_id_to_student_id($cwu_id)
	{
		$query_string = "
		SELECT
			COALESCE(id,0) AS id
		FROM 
			Students
		WHERE
			cwu_id=$cwu_id
		;";
		$query_result = my_query($query_string, false);
		$row = mysqli_fetch_assoc($query_result);

		return $row['id'];
	}

	function get_student_info($id, $cwu_id=0, $email='')
	{
		$student_info = array();
		if ($id != 0)
		{
			$where = "Students.id=$id";
		}
		else if ($cwu_id != 0)
		{
			$where = "Students.cwu_id=$cwu_id";
		}
		else if ($email != '')
		{
			$where = "Students.email='$email'";
		}

		$query_string = "
		SELECT
			id, cwu_id, CONCAT(first, ' ', last) AS name, email, first, last, active, phone, address, postbaccalaureate, non_stem_majors, withdrawing, veterans_benefits
		FROM
			Students
		WHERE
			$where
			;";
		$query_result = my_query($query_string, false);
		$info = mysqli_fetch_assoc($query_result);
/*
		$query_string = "
			SELECT
				Majors.name
			FROM
				Majors JOIN Student_Majors ON Majors.id=Student_Majors.major_id JOIN Students ON Students.id=Student_Majors.student_id
			WHERE
				$where
			;";
		$query_result = my_query($query_string, false);

		$program_array = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$program_array[] = $row['name'];
		}

		$info['math_majors'] = implode(", ", $program_array);
*/
		return $info;
	}

	function get_student_program_advisor($student_id, $program_id)
	{
		$query_string = "
		SELECT
			Users.id,
			Users.name,
			Users.login
		FROM
			Student_Programs
			JOIN Users ON Student_Programs.user_id=Users.id
		WHERE
			student_id=$student_id
			AND
			Student_Programs.program_id=$program_id
		;";
		$query_result = my_query($query_string, false);

		return mysqli_fetch_assoc($query_result);
	}

	function add_student($user_id, $cwu_id, $email, $first='', $last='')
	{
		global $link;
		if ($cwu_id != 0)
		{
			$query_string = "
			SELECT
				id
			FROM
				Students
			WHERE
				cwu_id=$cwu_id
			;";

			$query_result = my_query($query_string, false);

			if (mysqli_num_rows($query_result) > 0)
			{
				$row = mysqli_fetch_assoc($query_result);
				return $row['id'];
			}
		}
		else if ($email == '')
		{
			return 0;
		}

		$query_string = "
		INSERT INTO Students
			(cwu_id, email, first, last)
		VALUES
			($cwu_id, '$email', '$first', '$last')
		;";
		$result = my_query($query_string, false);

		$student_id = mysqli_insert_id($link);

		if ($student_id > 0)
		{
			record_update_student($user_id, $student_id, "Added <student:$student_id>");
		}
		else
		{
			add_message("Cannot add student, check for duplicate id ($cwu_id) or email ($email)");
		}

		return $student_id;
	}

	function update_student($user_id, $student_id, $first, $last, $cwu_id, $email, $phone, $address, $postbaccalaureate, $withdrawing, $veterans_benefits, $active)
	{
		global $link;
		$query_string = "
		UPDATE
			Students
		SET
			first='$first',
			last='$last',
			cwu_id=$cwu_id,
			email='$email',
			phone='$phone',
			address='$address',
			postbaccalaureate='$postbaccalaureate',
			withdrawing='$withdrawing',
			veterans_benefits='$veterans_benefits',
			active='$active'
		WHERE
			id=$student_id
			;";

		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Updated <student:$student_id>.";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function update_student_advisor($user_id, $student_id, $program_id, $advisor_id)
	{
		global $link;
		$query_string = "
		UPDATE
			Student_Programs
		SET
			user_id=$advisor_id
		WHERE
			student_id=$student_id
			AND
			program_id=$program_id
		;";

		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Set advisor to <user:$advisor_id> for <student:$student_id> in <program:$program_id>.";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function update_student_program($student_id, $major_id, $advisor, $catalog_year, $graduation_year)
	{
		remove_student_major($student_id, $major_id);

		$query_string = "
		INSERT INTO
			Student_Majors(student_id, major_id, advisor, catalog_year, graduation_year)
		VALUES
			($student_id, $major_id, '$advisor', $catalog_year, $graduation_year)
		;";

		$query_result = my_query($query_string, false);
	}

	function update_student_programs($user_id, $student_id, $remove_programs, $add_program_id, $add_advisor_id, $non_stem_majors)
	{
		global $link;
		//! @todo start update

		foreach ($remove_programs as $program_id)
		{
			$query_string = "
			DELETE FROM
				Student_Programs
			WHERE
				student_id = $student_id
				AND
				program_id = $program_id
			;";

			$query_result = my_query($query_string, false);

//			if (mysqli_affected_rows($link) > 0)
//			{
//				//! @todo record removal
//			}
		}

		if ($add_program_id != 0)
		{
			$query_string = "
			INSERT INTO
				Student_Programs(student_id, program_id, user_id)
			VALUES
				($student_id, $add_program_id, $add_advisor_id)
			;";

			$query_result = my_query($query_string, false);

//			if (mysqli_affected_rows($link) > 0)
//			{
//				//! @todo record addition
//			}
		}

		$query_string = "
		UPDATE
			Students
		SET
			non_stem_majors='$non_stem_majors'
		WHERE
			id=$student_id
		;";

		$query_result = my_query($query_string, false);

//		if (mysqli_affected_rows($link) > 0)
//		{
//			//record update
//		}
	}

	function remove_student_major($student_id, $major_id)
	{
		$query_string = "
		SELECT
			catalog_year
		FROM
			Student_Majors
		WHERE
			student_id=$student_id
			AND
			major_id=$major_id
		;";
		$query_result = my_query($query_string, false);
		$row = mysqli_fetch_assoc($query_result);
		$catalog_year = $row['catalog_year'];

		$program_id = get_program_id($major_id, $catalog_year);

		$query_string = "
		DELETE
			Electives
		FROM
			Electives JOIN Student_Classes ON (Electives.student_class_id=Student_Classes.id)
		WHERE
			program_id=$program_id
			AND
			student_id=$student_id
		;";
		$query_result = my_query($query_string, false);

		$query_string = "
		DELETE
			Student_Checklists
		FROM
			Student_Checklists JOIN Checklists ON (Student_Checklists.checklist_id=Checklists.id)
		WHERE
			student_id=$student_id
			AND
			program_id=$program_id
		;";
		$query_result = my_query($query_string, false);

		$query_string = "
		DELETE FROM
			Student_Majors
		WHERE
			student_id=$student_id
			AND
			major_id=$major_id
		;";
		$query_result = my_query($query_string, false);
	}

	function clear_plan($user_id, $student_id)
	{
		global $link;
		$query_string = "
		DELETE FROM
			Student_Classes
		WHERE
			student_id='$student_id'
			AND term != '000'
			;";
		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "<student:$student_id> plan cleared.";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function add_student_class($user_id, $student_id, $class_id, $term)
	{
		global $link;
		$query_string = "
		INSERT INTO
			Student_Classes(student_id, class_id, term)
		VALUES
			($student_id, $class_id, $term)
		;";
		$result = my_query($query_string, false);
		$student_class_id = mysqli_insert_id($link);
		if ($student_class_id)
		{
			$note = "<class:$class_id> added to <student:$student_id> in <term:$term>.";
			record_update_student($user_id, $student_id, $note);
		}
		return $student_class_id;
	}

	function add_student_elective($user_id, $student_class_id, $program_id)
	{
		$query_string = "
		INSERT INTO Electives
			(student_class_id, program_id)
		VALUES
			($student_class_id, $program_id)
		;";
		$query_result = my_query($query_string, false);

	}

	function update_plan($user_id, $student_id, $program_id, $classes)
	{
		$note = "Begin Update: <student:$student_id> plan.";
		record_update_student($user_id, $student_id, $note);

		clear_plan($user_id, $student_id);

		foreach($classes as $class_id => $data)
		{
			foreach ($data as $datum)
			{
				$term = $datum[0];
				$slot = $datum[1];
				$elective = $datum[2];

				$student_class_id = add_student_class($user_id, $student_id, $class_id, $term);

				if ($elective)
				{
					add_student_elective($user_id, $student_class_id, $program_id);
				}
			}
		}

		$note = "End Update: <student:$student_id> plan.";
		record_update_student($user_id, $student_id, $note);
	}

	function get_plan($student_id, $start_year, $end_year)
	{
		global $YES;
		global $NO;

		if ($start_year != 0 && $end_year != 0)
		for($year = $start_year; $year < $end_year; ++$year)
		{
			$classes_by_term[$year] = array(array(), array(), array(), array(), array());
		}
		$classes_by_id = array();

		$query_string = "
		SELECT
			Student_Classes.id AS student_class_id,
			Student_Classes.term,
			Classes.id
		FROM
			Student_Classes
			JOIN Classes ON Student_Classes.class_id=Classes.id
		WHERE
			Student_Classes.student_id=$student_id
		ORDER BY
			Student_Classes.term,
			Classes.name
			;";

		$query_result = my_query($query_string, false);

		while ($row = mysqli_fetch_assoc($query_result))
		{
			$term = $row['term'];
			$class_id = $row['id'];
			$student_class_id = $row['student_class_id'];

			$catalog_year = substr($term, 0,4);
			$catalog_term = substr($term, 4,1);

			if (!isset($classes_by_term[$catalog_year]))
			{
				$classes_by_term[$catalog_year] = array(array(), array(), array(), array(), array());
			}

			if ($term != 000)
			{
				$classes_by_term[$catalog_year][$catalog_term][] = array('student_class_id' => $student_class_id, 'class_id' => $class_id);
			}
			$classes_by_id[$class_id] = $term;
		}

		ksort($classes_by_term);

		$prev_year = 0;
		foreach ($classes_by_term as $year => $classes)
		{
			if ($prev_year != 0 && $prev_year != 0)
			{
				while($prev_year < $year - 1)
				{
					$prev_year++;
					$classes_by_term[$prev_year] = array(array(), array(), array(), array(), array());
				}
			}
			$prev_year = $year;
		}

		ksort($classes_by_term);

		return array('by term' => $classes_by_term, 'by id' => $classes_by_id);
	}

	function get_notes($student_id)
	{
		$query_string = "
		SELECT
			Notes.id,
			datetime,
			note,
			flagged,
			name
		FROM
			Notes JOIN Users ON Notes.user_id=Users.id
		WHERE
			Notes.student_id=$student_id
		ORDER BY
			Notes.flagged, Notes.datetime DESC
		;";
		$query_result = my_query($query_string, false);

		$notes = array();
		while($row = mysqli_fetch_assoc($query_result))
		{
			if ($row['name'] == '')
			{
				$tag = date('M j Y @ g:i a', strtotime($row['datetime']));
			}
			else
			{
				$tag = date('M j Y @ g:i a', strtotime($row['datetime']))." &mdash; ".$row['name'];
			}
			$note = $row['note'];
			$flagged = $row['flagged'];
			$notes[$row['id']] = array('tag' => $tag, 'note' => $note, 'flagged' => $flagged);
		}

		return $notes;
	}

	function add_note($user_id, $student_id, $note, $flagged)
	{
		global $link;
		global $YES;
		global $NO;

		$escaped_note = mysqli_real_escape_string($link, $note);

		$flagged_text = ($flagged ? $YES : $NO);
		$query_string = "
		INSERT INTO Notes
			(user_id, student_id, note, flagged, datetime)
		VALUES
			($user_id, $student_id, '$escaped_note', '$flagged_text', NOW())
		";
		$query_result = my_query($query_string, false);

		$note_id = mysqli_insert_id($link);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "<note:$note_id> added to <student:$student_id>.";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function update_notes($student_id, $flagged_ids)
	{
		global $YES;
		global $NO;

		$query_string = "
		UPDATE Notes
		SET
			flagged='$NO'
		WHERE
			student_id=$student_id
		;";
		$query_result = my_query($query_string, false);

		foreach ($flagged_ids as $flagged_id)
		{
			$query_string = "
			UPDATE Notes
			SET
				flagged='$YES'
			WHERE
				id=$flagged_id
			;";
			$query_result = my_query($query_string, false);
		}
	}

	function update_requirements($student_id, $requirements_taken)
	{
		$query_string = "
		DELETE FROM
			Student_Classes
		WHERE
			student_id = $student_id
			AND
			term = 000
		;";
		$query_result = my_query($query_string, false);

		foreach ($requirements_taken as $requirement_id)
		{
			$query_string = "
			INSERT INTO Student_Classes
				(student_id, class_id, term)
			VALUES
				($student_id, $requirement_id, 000)
			;";
			$query_result = my_query($query_string, false);
		}
	}

//	function students_in_program($program_id)
//	{
//		//! @todo this is failing and causing students.php to fail
//		return;
//		$query_string = "
//		SELECT
//			Students.id,
//			CONCAT(COALESCE(last,'*'), ', ', COALESCE(first,'*'), ' (', cwu_id, ')') AS name
//		FROM
//			Students
//			JOIN
//			Student_Programs
//			ON Students.id=Student_Programs.student_id
//		WHERE
//			cwu_id != 0
//			AND
//			Student_Programs.program_id=$program_id
//		ORDER BY
//			active, last, first ASC
//		;";
//
//		$query_result = my_query($query_string, false);
//
//		$all_students = array();
//		while ($row = mysqli_fetch_assoc($query_result))
//		{
//			$id = $row['id'];
//			$name = $row['name'];
//			$all_students[$id] = $name;
//		}
//
//		return $all_students;
//	}

	function students_for_user($user_id)
	{
		$query_string = "
		SELECT
			Students.id,
			CONCAT(COALESCE(last,'*'), ', ', COALESCE(first,'*'), ' (', cwu_id, ')') AS name
		FROM
			Students
			JOIN
			Student_Programs
			ON Students.id=Student_Programs.student_id
		WHERE
			cwu_id != 0
			AND
			Student_Programs.user_id=$user_id
		ORDER BY
			active, last, first ASC
		;";

		$query_result = my_query($query_string, false);

		$all_students = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$id = $row['id'];
			$name = $row['name'];
			$all_students[$id] = $name;
		}

		return $all_students;
	}

	function all_students($active_only = false)
	{
		$query_string = "
		SELECT
			id,
			CONCAT(COALESCE(last,'*'), ', ', COALESCE(first,'*'), ' (', cwu_id, ')') AS name
		FROM
			Students
		WHERE
			cwu_id != 0
		ORDER BY
			active, last, first ASC
			;";
		if ($active_only)
		{
			$query_string = "
			SELECT
				id,
				CONCAT(COALESCE(last,'*'), ', ', COALESCE(first,'*'), ' (', cwu_id, ')') AS name
			FROM
				Students
			WHERE
				cwu_id != 0
				AND
				active = 'Yes'
			ORDER BY
				last, first ASC
				;";
		}

		$query_result = my_query($query_string, false);

		$all_students = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$id = $row['id'];
			$name = $row['name'];
			$all_students[$id] = $name;
		}

		return $all_students;
	}

	function get_electives_credits($student_id, $program_id)
	{
		$query_string = "
		SELECT
			Classes.id AS class_id,
			Classes.name AS short_name,
			CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name,
			Classes.title,
			Classes.credits,
			Classes.fall,
			Classes.winter,
			Classes.spring,
			Classes.summer,
			Student_Classes.term,
			Student_Classes.id,
			Electives.id AS elective_id
		FROM
			Electives
			JOIN Student_Classes ON Electives.student_class_id=Student_Classes.id
			JOIN Classes ON Student_Classes.class_id = Classes.id
		WHERE
			Student_Classes.student_id = $student_id
			AND
			Electives.program_id = $program_id
		;";
		$query_result = my_query($query_string, false);

		$credits = 0;
		$electives = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$electives[$row['id']] = $row;
			$credits += $row['credits'];
		}

		return array('electives' => $electives, 'credits' => $credits);
	}

	function fill_template($user_id, $student_id, $template_id, $template_year)
	{
		global $link;
		$changed = false;
		if ($template_id != 0)
		{
			$query_string = "
			SELECT
				class_id, quarter, year
			FROM
				Template_Classes
			WHERE
				template_id=$template_id
				AND
				year > 0
			;";
			$query_result = my_query($query_string, false);

			while ($row = mysqli_fetch_assoc($query_result))
			{
				$class_id = $row['class_id'];
				$qtr = $row['quarter'];
				$yr = $template_year + ($row['year'] - 1);
				$term = "$yr$qtr";

				$query_string = "
					INSERT INTO
						Student_Classes(student_id, class_id, term)
					VALUES
						($student_id, $class_id, $term)
					;";

				my_query($query_string, false);
				$changed = $changed || (mysqli_affected_rows($link) > 0);
			}
		}
		if ($changed)
		{
			$note = "Filled <student:$student_id> with <template:$template_id>";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function get_checked_items($student_id, $program_id)
	{
		$query_string = "
		SELECT
			Student_Checklists.checklist_id
		FROM
			Student_Checklists JOIN Checklists ON Student_Checklists.checklist_id=Checklists.id
		WHERE
			student_id=$student_id
			AND
			program_id=$program_id
		;";
		$query_result = my_query($query_string, false);

		$checked_items = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$checked_items[] = $row['checklist_id'];
		}

		return $checked_items;
	}

	function clear_checklist($user_id, $student_id, $program_id)
	{
		global $link;
		$query_string = "
		DELETE
			Student_Checklists
		FROM
			Student_Checklists JOIN Checklists ON Student_Checklists.checklist_id=Checklists.id
		WHERE
			student_id=$student_id
			AND
			program_id=$program_id
		;";
		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Cleared <student:$student_id> checklists for <program:$program_id>.";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function check_checklist($user_id, $student_id, $checklist_id)
	{
		global $link;
		$query_string = "
		INSERT INTO
			Student_Checklists(checklist_id, student_id)
		VALUES
			($checklist_id, $student_id)
		;";
		$query_result = my_query($query_string, false);

		if (mysqli_affected_rows($link) > 0)
		{
			$note = "Checked <checklist_item:$checklist_id> for <student:$student_id>.";
			record_update_student($user_id, $student_id, $note);
		}
	}

	function update_checklist($user_id, $student_id, $program_id, $checklist_ids)
	{
		clear_checklist($user_id, $student_id, $program_id);

		foreach ($checklist_ids as $checklist_id)
		{
			check_checklist($user_id, $student_id, $checklist_id);
		}
	}

	function get_lost_students()
	{
		global $NO, $YES;

		//! @todo need to limit to the future
		$query_string = "
		SELECT
			Student_Classes.term,
			CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS class_name,
			CONCAT(Students.first, ' ', Students.last) AS student_name,
			Students.cwu_id,
			Classes.id AS class_id
		FROM
			Student_Classes
			JOIN Classes ON Student_Classes.class_id=Classes.id
			JOIN Students ON Student_Classes.student_id=Students.id
		WHERE
			(
				(
					RIGHT(term,1) = '1'
					AND Classes.fall = '$NO'
				)
				OR
				(
					RIGHT(term,1) = '2'
					AND Classes.winter = '$NO'
				)
				OR
				(
					RIGHT(term,1) = '3'
					AND Classes.spring='$NO'
				)
				OR
				(
					RIGHT(term,1) = '4'
					AND Classes.summer = '$NO'
				)
			)	
			AND
				LEFT(term,4) >= YEAR(CURDATE())	
			AND
				Students.active = '$YES'
			ORDER BY
				term
		;";
		$query_result = my_query($query_string, false);

		$info = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$info[] = $row;
		}

		return $info;
	}

	function get_bad_cwu_ids()
	{
		$query_string = "
		SELECT
			cwu_id,
			CONCAT(first, ' ', last) AS name,
			email,
			active
		FROM
			Students
		WHERE
			cwu_id != 0
			AND
			(
				cwu_id < 10000000
				OR
				cwu_id > 99999999
			)
		;";
		$query_result = my_query($query_string, false);

		$info = array();
		while ($row = mysqli_fetch_assoc($query_result))
		{
			$info[] = $row;
		}

		return $info;
	}


//-----------------------------------------------------
//! TERMS
//-----------------------------------------------------

	function get_enrollments($year)
	{
		global $YES;
		$year1 = 10*$year+1;
		$year2 = 10*($year)+2;
		$year3 = 10*($year)+3;
		$year4 = 10*($year)+4;

		$query_string = "
SELECT
    Classes.id,
    Classes.name,
    Student_Classes.term,
    CONCAT(Classes.name, ' (', Classes.credits, ' cr)') AS name_credits,
    COUNT(Student_Classes.student_id) AS enrollment
FROM
    Classes
JOIN
    Student_Classes ON Classes.id=Student_Classes.class_id
JOIN
    Students ON Students.id=Student_Classes.student_id
WHERE
    (
        Student_Classes.term='$year1'
        OR Student_Classes.term='$year2'
        OR Student_Classes.term='$year3'
        OR Student_Classes.term='$year4'
    )
    AND Students.active='$YES'
GROUP BY
    Classes.id, Classes.name, Classes.credits, Student_Classes.term
ORDER BY
    Classes.name ASC,
    Student_Classes.term
;";

		$result = my_query($query_string, false);
		
		$enrollments = array();
		while ($row = mysqli_fetch_assoc($result))
		{
			$class_id = $row['id'];
			$term_number = substr($row['term'],-1);
			if (!isset($enrollments[$class_id]))
			{
				$enrollments[$class_id] = array('name' => $row['name_credits'], 'enrollment' => array());
			}
			$enrollments[$class_id]['enrollment'][$term_number] = $row['enrollment']; 
		}
		
		return $enrollments;
	}
	
	function get_enrollments_by_program($year, $program_id)
	{
		global $YES;
		$year1 = 10 * $year + 1;
		$year2 = 10 * $year + 2;
		$year3 = 10 * $year + 3;
		$year4 = 10 * $year + 4;
	
		$query_string = "
	SELECT
		C.id,
		C.name,
		SC.term,
		CONCAT(C.name, ' (', C.credits, ' cr)') AS name_credits,
		COUNT(SC.student_id) AS enrollment
	FROM
		Classes C
	JOIN
		Student_Classes SC ON C.id = SC.class_id
	JOIN
		Students S ON S.id = SC.student_id
	JOIN
		Program_Classes PC ON C.id = PC.class_id
	WHERE
		(
			SC.term = '$year1'
			OR SC.term = '$year2'
			OR SC.term = '$year3'
			OR SC.term = '$year4'
		)
		AND S.active = '$YES'
		AND PC.program_id = '$program_id' -- Filter by program ID
	GROUP BY
		C.id, C.name, C.credits, SC.term
	ORDER BY
		C.name ASC,
		SC.term
	;";
	
		$result = my_query($query_string, false);
		
		$enrollments = array();
		while ($row = mysqli_fetch_assoc($result))
		{
			$class_id = $row['id'];
			$term_number = substr($row['term'], -1);
			if (!isset($enrollments[$class_id]))
			{
				$enrollments[$class_id] = array('name' => $row['name_credits'], 'enrollment' => array());
			}
			$enrollments[$class_id]['enrollment'][$term_number] = $row['enrollment']; 
		}
		
		return $enrollments;
	}
	

?>
