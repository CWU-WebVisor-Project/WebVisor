<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel='stylesheet' type='text/css' href='_style.css' />
    <link href="https://cdn.jsdelivr.net/npm/select2/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2/dist/js/select2.min.js"></script>
    <?php

    include_once("_html.php");
    include_once("_sql.php");
    global $YES;
    $user_info = get_user_info();
    if (is_array($user_info)) {
        $user_id = $user_info['id'];
        $superuser = is_superuser($user_info);
    } else {
        // Handle the error or set default values
        $user_id = null; // or a default/fallback value
        $superuser = false; // Assuming false as a default if not a superuser
    }


    if (!$user_info || !$superuser)
    {
        echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
        echo("</head>\n");
        echo("</html>\n");
        die();
    }

    $class_id = extract_int($_GET, 'id', extract_int($_POST, 'id', 0));
    $name = '';
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

        $required_grades = extract_id_values('grade', $_POST); // all grades in the system
        $selectedClassIds = isset($_POST['update_prereqs']) ? (array)$_POST['update_prereqs'] : array();
        $deleted_ids = extract_ids('delete', $_POST); // prereqs we are removing

        $core_ids = array_keys($required_grades);
        $core_ids = array_merge($core_ids, $selectedClassIds);
        $core_ids = array_diff($core_ids, $deleted_ids);

    /* UPDATE PREREQ GRADES*/
        // Fetch current prerequisites from the database
        $current_prereqs = get_prereqs($class_id);

        // Prepare an array to track which grades have changed
        $changed_grades = [];

        // Compare each current prerequisite's grade to the new grade from $_POST
        foreach ($current_prereqs as $prereq) {
            $prereq_id = $prereq['prerequisite_id'];
            $current_grade = $prereq['minimum_grade'];

            // Check if this prerequisite's grade has been posted and has changed
            if (isset($required_grades[$prereq_id]) && $required_grades[$prereq_id] != $current_grade) {
                // Store the new grade for updating
                $changed_grades[$prereq_id] = $required_grades[$prereq_id];
            }
        }

        // Update the database for any changed grades
        foreach ($changed_grades as $prereq_id => $new_grade) {
            updatePrerequisiteGrade($class_id, $prereq_id, $new_grade);
        }
    /* DELETE PREREQS */
        // Assuming deletePrerequisite function is correctly implemented as shown previously
        foreach ($deleted_ids as $deleted_id) {
            deletePrerequisite($class_id, $deleted_id);
        }
    /* ADD PREREQS */
        // Loop through each selected class ID and add it as a prerequisite
        foreach ($selectedClassIds as $selectedClassId) {
            // Assuming $studentClassId and $programId need to be determined or are known

            $minimumGrade = '20';

            // Call the function to add each prerequisite
            addPrerequisite($class_id, $selectedClassId, $minimumGrade);
        }
    }
    // Function to update a prerequisite's minimum grade in the database
    function updatePrerequisiteGrade($classId, $prereqId, $minimumGrade)
    {
        global $link; // Ensure your database connection is available

        $query = "UPDATE prerequisites SET minimum_grade = ? WHERE class_id = ? AND prerequisite_id = ?";
        $stmt = mysqli_prepare($link, $query);

        mysqli_stmt_bind_param($stmt, 'sii', $minimumGrade, $classId, $prereqId);

        if (!mysqli_stmt_execute($stmt)) {
            echo "Error updating prerequisite grade: " . mysqli_error($link);
        }

        mysqli_stmt_close($stmt);
    }
    function addPrerequisite($classId, $prerequisiteId, $minimumGrade) {
        global $link; // Assuming $link is your database connection variable

        // Prepare the SQL query to insert the new prerequisite
        // Note: 'id' is not included in the columns list as it auto-increments
        $query = "INSERT INTO prerequisites (class_id, prerequisite_id, minimum_grade) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($link, $query);

        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt, 'iii', $classId, $prerequisiteId, $minimumGrade);

        // Execute the statement and check for success
        if (mysqli_stmt_execute($stmt)) {
            //echo "Prerequisite added successfully.";
        } else {
            //echo "Error adding prerequisite: " . mysqli_error($link);
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }

    function deletePrerequisite($class_id, $deleted_id) {
        global $link;
        // Assuming $mysqli is your database connection
        // and $class_id and $prerequisite_id are already defined and validated variables

        // The SQL statement for deletion
            $sql = "DELETE FROM prerequisites WHERE class_id = ? AND prerequisite_id = ?";

        // Prepare the statement
            $stmt = $link->prepare($sql);

            if ($stmt) {
                // Bind the parameters to the statement
                $stmt->bind_param("ii", $class_id, $deleted_id); // 'ii' denotes that both parameters are integers

                // Execute the statement
                if ($stmt->execute()) {
                } else {
                    echo "Error deleting record: " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $link->error;
            }
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
            <td colspan="2"><input type='text' class='nameid' name='update_name' value='<?php echo $name; ?>' /></td>
        </tr>
        <tr>
            <td>Name:</td>
            <td colspan="2">
                <input type='text' class='nameid' name='update_title' value='<?php echo $title; ?>' /></td>
        </tr>
        <tr>
            <td>Credits:</td>
            <td colspan="2">

                <?php echo array_menu(" ", $all_credits, 'update_credits', $credits); ?>
            </td>
        </tr>
        <tr>
            <td>Offered:</td>
            <td colspan="2">
                <label class='checkbox'>Fall <?php echo checkbox(" ", 'update_fall', $fall == $YES); ?></label>
                <label class='checkbox'>Winter <?php echo checkbox(" ", 'update_winter', $winter == $YES); ?></label>
                <label class='checkbox'>Spring <?php echo checkbox(" ", 'update_spring', $spring == $YES); ?></label>
                <label class='checkbox'>Summer <?php echo checkbox(" ", 'update_summer', $summer == $YES); ?></label>
            </td>
        </tr>
        <tr class="header">
            <td>Prerequisites:</td>
            <td colspan="2"></td>
        </tr>
        <tr class="header">
            <td align="center">Name</td>
            <td align="center">Minimum Grade</td>
            <td align="center">Delete</td>
        </tr>
        <?php
        $row_num = 1;
        foreach ($prereqs as $id => $info) {
            $name = htmlspecialchars($info['name']);
            $min = $info['minimum_grade'];
            $class = ($row_num % 2) == 1 ? "class='alt'" : "";
            $row_num++;
            ?>
            <tr class="<?php echo $class; ?>">
                <td <?php echo($class); ?> align='center'><?php echo $name; ?></td>
                <td <?php echo($class); ?> align='center'>
                    <?php echo array_menu(" ", $all_grades, "grade-$id", $min); ?>
                </td>
                <td <?php echo($class); ?> align='center'>
                    <?php echo checkbox(" ", "delete-$id", false); ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td>Add Prerequisites:</td>
            <td colspan="2">
                <select multiple='multiple' name='update_prereqs[]'>
                    <?php foreach ($all_classes as $id => $name) {
                        if (!array_key_exists($id, $prereqs)) { ?>
                            <option value='<?php echo $id; ?>'><?php echo $name; ?></option>
                        <?php } } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Active:</td>
            <td colspan="2"><input type='checkbox' name='update_active' <?php if ($class_info['active'] == $YES) { echo "checked='checked'"; } ?>></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2"><input type='submit' name='update_class' value='Update Class Info' /></td>
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
                    if (isset($term[$term_number]) && is_array($term[$term_number]) && count($term[$term_number]) > 0)
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
                            <?php
                            if (isset($term[$term_number]) && is_array($term[$term_number]) && count($term[$term_number]) > 0)
                            {
                                echo(count($term[$term_number]));
                            }
                            ?>
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
