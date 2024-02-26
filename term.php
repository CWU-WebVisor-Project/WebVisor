<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php

    include_once("_html.php");
    include_once("_sql.php");

    //! @todo maybe allow anonymous access here since there is no updating
    if (!get_user_info()) {
        echo("<meta http-equiv='refresh' content='0; url=settings.php' />\n");
        echo("</head>\n");
        echo("</html>\n");
        die();
    }

    $curr_year = date('Y');
    $year = extract_int($_GET, 'year', $curr_year);
    $years = [];
    //! @todo should go back to previous year if we are in Winter or Spring
    for ($i = $curr_year - 1; $i < $curr_year + 10; ++$i) {
        $years[$i] = "$i";
    }

    if (isset($_GET['year']) && array_key_exists($_GET['year'], $years)) {
        $year = $_GET['year'];
    } else {
        $year = $curr_year - 1; // Default to the earliest year in the range
    }

    //array of majors
    $all_programs_blank = [];
    $all_programs_blank = array(0 => '') + all_programs();
    $program_id = extract_int($_GET, 'program_id');

    // Check if program_id is selected in URL parameters
    if (isset($_GET['program_id']) && array_key_exists($_GET['program_id'], $all_programs_blank)) {
        $program_id = $_GET['program_id'];
    } else {
        // Default to an empty program if no program is selected
        $program_id = 0;
    }


    ?>
    <title>Enrollments</title>
    <link rel='stylesheet' type='text/css' href='_style.css'/>
</head>
<body>

<?php echo(messages()); ?>
<?php echo(linkmenu()); ?>

<h1>Enrollments</h1>

<table>
    <tr>
        <td style="vertical-align: top;">
            <form action='' method='get'>
                <?php echo(array_menu('Year Starting Fall: ', $years, 'year', $year, true));?>
                <?php echo(array_menu('Majors: ', $all_programs_blank, 'program_id',$program_id ,true)); ?>
            </form>
        </td>



        <?php 
            if ($program_id == 0){
                $enrollments = get_enrollments($year);
                //echo '<pre>'; print_r($enrollments); echo '</pre>';
            } else {
                $enrollments = get_enrollments_by_program($year,$program_id);
            }
        
        ?>



    </tr>
</table>

<table>
    <tr>
        <td>Class Name</td>
        <td style='padding:0px 10px;'>Fall <?php echo($year); ?></td>
        <td style='padding:0px 10px;'>Winter <?php echo($year + 1); ?></td>
        <td style='padding:0px 10px;'>Spring <?php echo($year + 1); ?></td>
        <td style='padding:0px 10px;'>Summer <?php echo($year + 1); ?></td>
    </tr>
    <?php
    foreach ($enrollments as $class_id => $info) {
        $name = $info['name'];
        $enrollment = $info['enrollment'];
        $class = '';
        if ($class == 'even') {
            $class = 'odd';
        } else {
            $class = 'even';
        }
        ?>
        <tr class='<?php echo($class); ?>'>
            <td><a href='class.php?id=<?php echo($class_id); ?>'><?php echo($name); ?></a></td>
            <?php
            for ($i = 1; $i < 5; ++$i) {
                ?>
                <td style='text-align:center;'><a
                            href='roster.php?class_id=<?php echo($class_id); ?>&amp;term=<?php echo($year . $i); ?>'>
                        <?php

                        if (isset($enrollment[$i])) {

                            echo($enrollment[$i]);
                        } else {
                            echo(" ");
                        }

                        ?></a></td>
                <?php
            }
            ?>
        </tr>
        <?php
    
    }
    ?>


</table>

</body>
</html>
