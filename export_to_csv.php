<?php
function exportTableToCSV($tableData, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w'); // Open PHP output stream for writing CSV data

    // If the table data has headers included, output those first
    if (isset($tableData['headers'])) {
        fputcsv($output, $tableData['headers']);
    }

    // Output the rows of the table
    foreach ($tableData['rows'] as $row) {
        fputcsv($output, $row);
    }

    fclose($output); // Close the output stream
    exit;
}

// If this script is called with a POST request to trigger the CSV download
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['export_csv'])) {
    // Assume you have a function that retrieves your table data as an array
    $tableData = getTableData(); // This function needs to be defined by you
    exportTableToCSV($tableData);
}

// This should be part of your `export_to_csv.php` or included in it
function getTableData() {
    // Your code to fetch data and return it as an array.
    // For example, this could come from a database query.
    // The array should be formatted with 'headers' for the CSV headers
    // and 'rows' for each row of data.
    return [
        'headers' => ['Name', 'CWU ID', 'Email', 'Advisor'], // Replace with actual headers
        'rows' => [
            // Replace these rows with actual data
            ['John Doe', '123456', 'johndoe@cwu.edu', 'Advisor Name'],
            ['Jane Smith', '654321', 'janesmith@cwu.edu', 'Adv']
        ]
    ];
}
?>
