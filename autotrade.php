<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

// Require the Composer autoloader
require_once('vendor/autoload.php');

// Constants for database connection details
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'databank');

// Constant for WebDriver host URL
define('WEBDRIVER_HOST', 'http://localhost:4444/');

// Function to establish a database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

try {
    // Set Chrome capabilities and create WebDriver session
    $capabilities = DesiredCapabilities::chrome();
    $driver = RemoteWebDriver::create(WEBDRIVER_HOST, $capabilities);

    // Maximize the browser window
    $driver->manage()->window()->maximize();

    // Navigate to the webpage
    $driver->get('https://pip.worldbank.org/poverty-calculator');

    // Wait for the specific <div> containing your elements
    $driver->wait(10)->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('div[joyridestep="thirdStep"][stepposition="top"]'))
    );

    sleep(10);

    // Process the radio buttons and dropdowns
    processRadioButtons($driver, $conn);

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
} finally {
    // Clean up WebDriver and database connection
    if (isset($driver)) {
        $driver->quit();
    }
    $conn->close();
}

function processRadioButtons($driver, $conn) {
    // Find the <div> element containing steps
    $divElement = $driver->findElement(WebDriverBy::cssSelector('div[joyridestep="thirdStep"][stepposition="top"]'));

    // Find all <ul> elements within the <div>
    $ulElements = $divElement->findElements(WebDriverBy::tagName('ul'));

    // Loop through each <ul> element
    foreach ($ulElements as $ulElement) {
        // Find all <li> elements within each <ul>
        $liElements = $ulElement->findElements(WebDriverBy::tagName('li'));

        // Loop through each <li> element
        foreach ($liElements as $liElement) {
            try {
                // Find the radio button within the <li> element
                $radioButton = $liElement->findElement(WebDriverBy::cssSelector('label'));

                // Click on the label to select the radio button
                $labelText = $liElement->getText();
                $radioButton->click();

                echo "Clicked on label: $labelText\n";

                // Process the dropdowns for each selected radio button
                processDropdowns($driver, $conn);

            } catch (Exception $e) {
                echo "Error clicking on label: " . $e->getMessage() . "\n";
            }
        }

        echo "-----------------------------------\n"; // Separator between <ul> elements
    }
}

function processDropdowns($driver, $conn) {
    for ($x = 0; $x < 2; $x++) {
        selectYearList($driver);
        $count = selectCountryList($driver, $x, true);
        echo "Count of County -- " . $count;
        for ($i = 0; $i < 100; $i++) {
            selectCountryList($driver, $i);
            selectTableTab($driver);
            fetchTableData($driver, $conn);
        }
    }
}

function selectYearList($driver) {
    // Click on the dropdown to open it
    $dropdown = $driver->findElement(WebDriverBy::cssSelector('.k-dropdown-wrap'));
    $dropdown->click();

    // Wait for dropdown options to load
    sleep(5);
}

function selectCountryList($driver, $index, $isCount = false) {
    $divElement = $driver->findElement(WebDriverBy::cssSelector('div[joyridestep="firstStep"]'));

    // Find and click the kendo-multiselect element within the div
    $multiSelectElement = $divElement->findElement(WebDriverBy::cssSelector('.k-widget.k-multiselect'));
    $multiSelectElement->click();

    // Wait for dropdown options to load
    sleep(1);

    // Find dropdown container and options
    $dropdownContainer = $driver->findElement(WebDriverBy::cssSelector('.k-animation-container'));
    $options = $dropdownContainer->findElements(WebDriverBy::cssSelector('ul li'));

    if ($isCount) {
        echo "Count option ----" . count($options);
        echo "Option: " . $options[$index]->getText() . "\n";
        $options[$index]->click();
        return count($options);
    } else {
        echo "Count option ----" . count($options);
        echo "Option: " . $options[$index]->getText() . "\n";
        $options[$index]->click();
    }

    // Output the count of options
    sleep(1);
}

function selectTableTab($driver) {
    try {
        $tableTab = $driver->findElement(WebDriverBy::id('table-tab'));
        $tableTab->click();
        echo "Clicked on TABLE tab.\n";
    } catch (Exception $e) {
        echo "Error clicking on TABLE tab: " . $e->getMessage() . "\n";
    }
}

function fetchTableData($driver, $conn) {
    try {
        $table = $driver->findElement(WebDriverBy::cssSelector('table.k-grid-table'));
        $tbodyElements = $table->findElements(WebDriverBy::tagName('tbody'));

        foreach ($tbodyElements as $tbody) {
            $rows = $tbody->findElements(WebDriverBy::tagName('tr'));
            foreach ($rows as $row) {
                $cells = $row->findElements(WebDriverBy::tagName('td'));
                $rowData = [];
                foreach ($cells as $cell) {
                    $rowData[] = $cell->getText();
                }
                // Prepare data for insertion
                $data = [
                    'year' => $rowData[0],
                    'region' => $rowData[1],
                    'level' => $rowData[2],
                    'poverty_rate' => $rowData[3],
                    'poverty_line' => $rowData[4],
                    'interpolated' => $rowData[5],
                    'datatype' => $rowData[6]
                ];
                insertData($conn, $data);
            }
        }
    } catch (Exception $e) {
        echo "Error fetching table data: " . $e->getMessage() . "\n";
    }
}

function recordExists($data) {
    $query = "SELECT COUNT(*) FROM pip_worldbank_records WHERE ";
    $query .= implode(" AND ", array_map(function($key) { return "`$key` = ?"; }, array_keys($data)));
    
    $stmt = $this->conn->prepare($query);
    
    // Bind parameters
    $stmt->bind_param(str_repeat("s", count($data)), ...array_values($data));
    
    // Execute query
    $stmt->execute();
    
    // Bind result variable
    $stmt->bind_result($count);
    
    // Fetch value
    $stmt->fetch();
    
    // Close statement
    $stmt->close();
    
    // Return true if count is greater than 0 (record exists), false otherwise
    return $count > 0;
}

function insertData($conn, $data) {
    if (!recordExists($conn, $data)) {
        $query = "INSERT INTO pip_worldbank_records (";
        $query .= implode(", ", array_map(function($key) { return "`$key`"; }, array_keys($data)));
        $query .= ") VALUES (";
        $query .= implode(", ", array_fill(0, count($data), "?"));
        $query .= ")";
        
        $stmt = $conn->prepare($query);
        
        // Check if prepare statement failed
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        
        // Bind parameters
        $stmt->bind_param(str_repeat("s", count($data)), ...array_values($data));
        
        // Execute query
        $stmt->execute();
        
        // Check if execute statement failed
        if ($stmt === false) {
            die('Execute failed: ' . htmlspecialchars($stmt->error));
        }
        
        // Close statement
        $stmt->close();
        
        echo "\n Data inserted successfully.\n";
    } else {
        echo "\n Duplicate record found for data: " . implode(', ', $data) . "\n";
    }
}

?>
