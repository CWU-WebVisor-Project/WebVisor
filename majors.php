<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2/dist/js/select2.min.js"></script>
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
    $roster = [];
	if ($program_id != 0)
	{
		$roster = get_program_roster($program_id);
	}
	
	$all_programs_blank = array(0 => '') + all_programs();

?>
</head>
<body>
<style>


    table {
        margin-top: 20px;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 8px;
        margin-top: 10px;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
        margin-top: 10px;
    }

    .select2-container {
        margin-bottom: 10px;
        margin-top: 10px;
        width: 20% !important;
    }

</style>

<?php
	echo(messages());
	echo(linkmenu());
?>

	<form action='majors.php' method='get'>
        <td>
            <select id="program_id" name="program_id" class="select2-class">
                <option value=""></option>
                <?php foreach ($all_programs_blank as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
		<br />
		<input type='submit' />
	</form>

<!-- THIS IS THE FORM METHOD TO ADD THE EXPORT BUTTON (FUTURE JAKE or FUTURE GROUPS)
    <form method="post" action="export_to_csv.php">
        <input type="hidden" name="export_csv" value="1">
        <button type="submit">Export to CSV</button>
    </form>
-->
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
<script>
    $(document).ready(function() {
        // Initialize the Select2 dropdown
        $('#program_id').select2({
            placeholder: "Select a program",
            allowClear: true
        });

        // Check if a previously selected value is stored and set it
        var selectedProgram = localStorage.getItem('selectedProgram');
        if (selectedProgram) {
            $('#program_id').val(selectedProgram).trigger('change');
        }

        // Save the selected value when it changes
        $('#program_id').on('change', function() {
            var selectedValue = $(this).val();
            localStorage.setItem('selectedProgram', selectedValue);
        });
    });
</script>

