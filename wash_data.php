<?php
set_time_limit(0);
require_once 'vendor/autoload.php';

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverExpectedCondition;

// Database credentials
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'scraped_data';

// Connect to the database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Chromedriver server URL
$serverUrl = 'http://localhost:9515';

// Chrome options
$options = new ChromeOptions();
$options->addArguments(['--window-size=1024,768']);

// Desired capabilities
$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

// Create a WebDriver instance
$driver = RemoteWebDriver::create($serverUrl, $capabilities, 5000);

// Navigate to the webpage
$driver->get('https://washdata.org/data/household#!/table?geo0=country&geo1=GHA');

// Wait for the geography link button to be visible and clickable
$driver->wait(40)->until(
    WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#geography-level-0 > button'))
);

// Click on the geography link button
$geographyLink = $driver->findElement(WebDriverBy::cssSelector('#geography-level-0 > button'));
$geographyLink->click();

echo "Clicked on Geography link" . PHP_EOL;

// Wait for the dropdown to load within a reasonable time
$driver->wait(40)->until(
    WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.multiselect-dropdown-list'))
);

// Find the dropdown list
$dropdownList = $driver->findElement(WebDriverBy::cssSelector('.multiselect-dropdown-list'));

// Find all options within the dropdown list
$options = $dropdownList->findElements(WebDriverBy::cssSelector('.dropdown-option a'));

echo "Geography options:" . PHP_EOL;
foreach ($options as $key => $option) {
    if ($key == 0) continue;
    // Extract text from each option
    $value = $option->getText();
    echo " - " . $value . PHP_EOL;
    $option->click();

    // Select region options here 
    $regionType = $driver->findElement(WebDriverBy::cssSelector('#geography-level-1 > button'));

    // Scroll the button into view
    $driver->executeScript('arguments[0].scrollIntoView(true);', [$regionType]);

    // Wait for the button to be clickable again
    $driver->wait(20)->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#geography-level-1 > button'))
    );

    // Click on the button
    $regionType->click();

    echo "Clicked on the button" . PHP_EOL;

    $regionDropdownList = $driver->findElement(WebDriverBy::cssSelector('#geography-level-1 .multiselect-dropdown-list'));

    // Find all <li> elements within the region dropdown list
    $regionLiElements = $regionDropdownList->findElements(WebDriverBy::cssSelector('li.dropdown-option'));

    echo "Region options:" . PHP_EOL;
    foreach ($regionLiElements as $regionLi) {
        // Find the <a> element within each <li> element
        $regionOption = $regionLi->findElement(WebDriverBy::cssSelector('a'));

        // Extract text from each region option
        $regionValue = $regionOption->getText();
        echo " - " . $regionValue . PHP_EOL;

        // Click on the region option
        $regionOption->click();


        // Check if the region already exists in the database
        insertRegionType($mysqli, $regionValue);


        //<<================================ year start ======================>>
        // Click on the time period multi button
$timePeriodButton = $driver->findElement(WebDriverBy::cssSelector('#time-period-multi > button'));
$timePeriodButton->click();
echo "Clicked on Time Period button" . PHP_EOL;

// Wait for the dropdown to load
$driver->wait(30)->until(
    WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#time-period-multi > div > div.dropdown-groups'))
);

// Find all dropdown groups within the time period dropdown
$dropdownGroups = $driver->findElements(WebDriverBy::cssSelector('#time-period-multi > div > div.dropdown-groups'));

echo "Time Period Dropdown Option:" . PHP_EOL;
foreach ($dropdownGroups as $dropdownGroup) {
    // Find the <a> element within each <li> element
    $yearOption = $dropdownGroup->findElement(WebDriverBy::cssSelector('a'));

    // Extract text from each region option
    $yearValue = $yearOption->getText();
    echo " - " . $yearValue . PHP_EOL;

    // Click on the region option
    $yearOption->click();

        //<<================================ year End ======================>>
sleep(20);
$driver->wait(40)->until(
    WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::cssSelector('table[data-table="ng"][ng-if="tableData"] tbody tr'))
);

// Find all rows (tr) in the table
$rows = $driver->findElements(WebDriverBy::cssSelector('table[data-table="ng"][ng-if="tableData"] tbody tr'));
print_r($rows);die;
// Iterate through each row
foreach ($rows as $row) {
    // Find all cells (td) within each row
    $cells = $row->findElements(WebDriverBy::tagName('td'));
    $recordData = [];

    // Iterate through each cell
    foreach ($cells as $cell) {
        // Collect text from each cell
        $recordData[] = $cell->getText();
    }

    // Insert record into database
    // insertRecord($mysqli, $recordData);
}

die;




        // Wait for the table to load
        $driver->wait(30)->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::cssSelector('tr[ng-repeat="record in tableData"]'))
        );

        // Find all rows (tr) with ng-repeat="record in tableData"
        $rows = $driver->findElements(WebDriverBy::cssSelector('tr[ng-repeat="record in tableData"]'));

        // Iterate through each row
        foreach ($rows as $row) {
            // Find all cells (td) within each row
            $cells = $row->findElements(WebDriverBy::tagName('td'));
            $recordData = [];

            // Iterate through each cell
            foreach ($cells as $cell) {
                // Collect text from each cell
                $recordData[] = $cell->getText();
            }

            // Insert record into database
            insertRecord($mysqli, $recordData);
        }
}
}
}

// Close the WebDriver session
$driver->quit();
echo "WebDriver session closed." . PHP_EOL;

// Function to insert a record into the database
function insertRecord($mysqli, $recordData)
{
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM record WHERE Type = ? AND Region = ?  AND residence_type = ?  AND service_type = ?  AND year = ?  AND coverage = ?  AND population = ?  AND service_lavel = ?");
    if ($stmt === false) {
        die('Error preparing statement: ' . $mysqli->error);
    }

    // Bind parameters
    $stmt->bind_param('ssssssss', $recordData[0], $recordData[1], $recordData[2], $recordData[3], $recordData[4], $recordData[5], $recordData[6], $recordData[7]);

    // Execute statement
    if (!$stmt->execute()) {
        die('Error executing statement: ' . $stmt->error);
    }

    // Fetch result count
    $stmt->store_result();
    $stmt->bind_result($count);
    $stmt->fetch();

    // Close statement
    $stmt->close();

    // Insert record if count is 0
    if ($count == 0) {
        $stmtInsert = $mysqli->prepare("INSERT INTO record (Type, Region, residence_type, service_type, year, coverage, population, service_lavel) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmtInsert === false) {
            die('Error preparing insertion statement: ' . $mysqli->error);
        }

        // Bind parameters for insertion
        $stmtInsert->bind_param('ssssssss', $recordData[0], $recordData[1], $recordData[2], $recordData[3], $recordData[4], $recordData[5], $recordData[6], $recordData[7]);

        // Execute insertion
        if (!$stmtInsert->execute()) {
            die('Error executing insertion statement: ' . $stmtInsert->error);
        }

        // Close insertion statement
        $stmtInsert->close();
    }
}

// Function to insert a region type into the database
function insertRegionType($mysqli, $regionValue)
{
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM region_type WHERE name = ?");
    if ($stmt === false) {
        die('Error preparing statement: ' . $mysqli->error);
    }

    // Bind parameter
    $stmt->bind_param('s', $regionValue);

    // Execute statement
    if (!$stmt->execute()) {
        die('Error executing statement: ' . $stmt->error);
    }

    // Fetch result count
    $stmt->store_result();
    $stmt->bind_result($count);
    $stmt->fetch();

    // Close statement
    $stmt->close();

    // Insert region type if count is 0
    if ($count == 0) {
        $stmtInsert = $mysqli->prepare("INSERT INTO region_type (name) VALUES (?)");
        if ($stmtInsert === false) {
            die('Error preparing insertion statement: ' . $mysqli->error);
        }

        // Bind parameter for insertion
        $stmtInsert->bind_param('s', $regionValue);

        // Execute insertion
        if (!$stmtInsert->execute()) {
            die('Error executing insertion statement: ' . $stmtInsert->error);
        }

        // Close insertion statement
        $stmtInsert->close();
    }
}
