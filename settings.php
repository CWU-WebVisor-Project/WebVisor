<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	include("_sql.php");
	include("_html.php");

    $user_info = array();
    $login = extract_string($_POST, 'user_login');
    $password = extract_string($_POST, 'user_password');

if(isset($_POST['logout'])) {
    setcookie('webvisor_login', '', time() - 1);
    setcookie('webvisor_password', '', time() - 1);
} else {
    $user_info = get_user_info($login, $password, true, true);
}

if ($user_info) {
    if (isset($_POST['update'])) {
        $new_password = extract_string($_POST, 'new_pass');
        $new_password_2 = extract_string($_POST, 'new_pass_2');
        $name = extract_string($_POST, 'advisor');
        $program_id = extract_int($_POST, 'program_id');

        if ($new_password === $new_password_2 && !empty($new_password)) {
            // Hash the new password before updating it
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            // If passwords do not match or new password is empty, keep the old password
            // Assuming you have the original password hashed in the database, in which case,
            // you need the hashed password here. If you only have the plain password, you'll need
            // to hash it as shown below. This is not a secure practice; it's better to also get the hashed password.
            $hashed_new_password = password_hash($password, PASSWORD_DEFAULT);
        }
        $user_id = $user_info['id'];
        update_user($user_id, $hashed_new_password, $name, $program_id);
        // After updating, get user info might need to work with hashed passwords or you adjust the logic accordingly
        $user_info = get_user_info($login, $password, '', true);
    }

    if (is_array($user_info)) {
        $program_id = $user_info['program_id'];
        $advisor_name = $user_info['name'];
    } else {
        // Handle the case where $user_info is not an array.
        // This might include setting default values or handling an error condition.
        $program_id = null; // or a default value
        $advisor_name = ''; // or a default value
    }

    $all_programs = array('0' => '') + all_programs();
}
?>
    <title>Settings</title>
    <link rel='stylesheet' type='text/css' href='_style.css' />
</head>
<body>

<?php
if (true || $connected) {
    echo(messages());
    echo(linkmenu());
}
?>

<h1>Settings</h1>

<?php if ($user_info) {
?>
	<p>If you see this, you are connected to the system. You can log out (and delete the cookie with your username and password) by selecting "Logout". This causes the browser you are using to delete the login information for you. You do not need to update anything on this page, but this is the page you would change it on if you decide to update the password.</p>
	
	<p>If you are not a superuser, you should only be able to access the <b>Student Information</b>, <b>Enrollments</b>, <b>Lost Students</b>, and <b>Settings</b> page.
<?php
}
?>

    <form method="post" onsubmit="return validatePassword()">
		<table class='input'>
<?php
	if (!$user_info)
	{
?>
			<tr>
				<td>User Login</td>
				<td><input type='text' name='user_login' value='<?php echo($login); ?>' /></td>
			</tr>
            <tr>
                <td>User Password</td>
                <td>
                    <input type='password' id='user_password' name='user_password' />
                    <button type="button" onclick="togglePasswordVisibility('user_password')">Show/Hide Password</button>
                </td>
            </tr>
            <tr>
				<td/>
				<td><input type='submit' name='login' value='Login' /></td>
			</tr>
<?php
	}
	else
	{
?>
			<tr>
				<td />
				<td><input type='submit' name='logout' value='Logout' /></td>
				<td />
			<tr>
			<tr>
				<td colspan='3' style='background-color:white;' />
            <tr>
                <td>Enter New Password</td>
                <td>
                    <input type='password' id='new_pass' name='new_pass' />
                    <button type="button" onclick="togglePasswordVisibility('new_pass')">Show/Hide Password</button>
                </td>
            </tr>
			<tr>
				<td>Retype New Password</td>
                <td>
                    <input type='password' id='new_pass2' name='new_pass2' />
                    <button type="button" onclick="togglePasswordVisibility('new_pass2')">Show/Hide Password</button>
                </td>
			</tr>
			<tr>
				<td>Advisor Name</td>
				<td><input type='text' name='advisor' value='<?php echo($advisor_name); ?>' /></td>
				<td />
			</tr>
			<tr>
				<td>Default Program</td>
				<td><?php echo(array_menu('', $all_programs, 'program_id', $program_id, false)); ?></td>
				<td />
			</tr>
			<tr>
				<td />
				<td><input type='submit' name='update' value='Update' /></td>
				<td />
			</tr>
<?php
	}
?>
		</table>

	</form>
<?php
// Assume is_superuser function definition is elsewhere and returns a boolean
if (is_superuser($user_info)) {
    echo '<h2>Add New User</h2>';
    ?>
    <form method="post" action="">
        <table class="input">
            <tr>
                <td>User Login</td>
                <td><input type="text" name="new_user_login" /></td>
            </tr>
            <tr>
                <td>User Password</td>
                <td>
                    <input type='password' id='new_user_pass' name='new_user_pass' />
                    <button type="button" onclick="togglePasswordVisibility('new_user_pass')">Show/Hide Password</button>
                </td>
            </tr>
            <tr>
                <td>Name</td>
                <td><input type="text" name="new_user_name" /></td>
            </tr>
            <tr>
                <td>First Name</td>
                <td><input type="text" name="new_user_first" /></td>
            </tr>
            <tr>
                <td>Last Name</td>
                <td><input type="text" name="new_user_last" /></td>
            </tr>
            <tr>
                <td>Role</td>
                <td>
                    <select name="new_user_role">
                        <option value="0">Not a Superuser</option>
                        <option value="1">Superuser</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" name="add_user" value="Add User" /></td>
            </tr>
        </table>
    </form>
    <?php
    if (isset($_POST['add_user']) && !empty($_POST['new_user_login']) && !empty($_POST['new_user_password'])) {
        // Ensuring all listed fields are set and not blank
        $requiredFields = ['new_user_login', 'new_user_password', 'new_user_name', 'new_user_first', 'new_user_last'];
        $allFieldsPresent = true;
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $allFieldsPresent = false;
                echo "<p>Error: '$field' is required and cannot be blank.</p>";
                break;
            }
        }

        if ($allFieldsPresent) {
            // Proceed with form processing
            $login = $_POST['new_user_login'];
            $password = $_POST['new_user_password']; // Consider hashing the password here
            $name = $_POST['new_user_name'];
            $first = $_POST['new_user_first'];
            $last = $_POST['new_user_last'];
            $superuser = ($_POST['new_user_role'] === '1') ? 'Yes' : 'No';

            // Call to add_user function
            add_user(null, $login, $password, $name, null, $superuser, $last, $first);
        }
    }
}
?>


<?php
	if (false)
	{
?>		
<h1>Help</h1>

<p>This will eventually expand to (hopefully) become useful. Right now it is just a place where I put ideas I don't want to forget.</p>

<h2>Majors</h2>
	<p>Majors typically consist of just a name (for example, <strong>Math BA, Teaching Secondary</strong>). There is no other information associated with a major.</p>
	
<h2>Programs</h2>
	<p>Programs are the level of organization that you will typically be using. You are free to create any programs that you may find useful and you are free to include or exclude anything that you feel will be useful to you while you are advising students. The most common program is identified by a Major and a catalog year.
		<ul>
			<li><strong>Major &amp; Year</strong> will tie the program to a major and catalog year. You should only create programs for catalog years when the program has changed. Eventually, a student will be compared to the most recent program in the major that is prior to their catalog year. At the moment, this information isn't being used.</li>
			<li><strong>Credits</strong> both the total credits and the elective credits. At the moment, the total credits is not being used. The elective credits is used to confirm that a student has enough elective credits in their plan to fulfill the major's requirements.</li>
			<li>Classes, including minimum grade required for the class to count toward the major. This can include both required classes and common electives. For each class, you can set the following:
				<ul>
					<li><strong>Sequencing</strong> will affect the order the classes appear in the dropdown class menus while working on this major</li>
					<li><strong>Minimum Grade</strong> allows you to identify classes for which a particular grade is required to continue in the major.</li>
					<li><strong>Required</strong> indicates whether the class is required for the major. The system should check whether a student plan contains all required classes.</li>
				</ul>
			</li>
			<li><strong>Templates</strong> are used to fill in standard class sequences. Each program can have as many templates as desired. For example, you may want 2-year, 3-year, and 4-year templates that fill in all required classes for the major over the specified time period. At the moment, templates always start in the Fall quarter of the starting year. This makes them less efficient for automatically inserting a sequence of courses (e.g., MATH 153-MATH 154-MATH 172) which might start on a different term.</li>
			<li><strong>Substitutions</strong> can be used to store common substitutions used in the program. These will allow requirement to be listed as satisfied without requiring the exact class be in the student's plan.</li>
			<li>The <strong>Checklist</strong> can contain anything you wish to track for every student in the program.</li>
		</ul>
	</p>
	
<h2>Classes</h2>
	<p>A class consists of:
		<ul>
			<li>a <strong>Catalog Designation</strong>, for example, MATH 153.</li>
			<li>a <strong>Name</strong>, for example, Precalculus I.</li>
			<li><strong>Credits</strong> are the number of credits earned in the class. If you have a variable credit course, you will want to create a different class in the system for each of the possible credit values.</li>
			<li>the quarters in which the class is <strong>Offered</strong>, the system will identify students whose plan has them enrolled in a class during a quarter in which it is not offered.</li>
			<li>the <strong>Prerequisites</strong> for the class. This is not currently used, but the system should eventually be able to determine when a student is taking the classes out of sequence.</li>
		</ul>
	
	
<?php
	}
?>	
</body>
</html>
<script>
    function togglePasswordVisibility(fieldId) {
        var field = document.getElementById(fieldId);
        if (field.type === "password") {
            field.type = "text";
        } else {
            field.type = "password";
        }
    }
</script>

