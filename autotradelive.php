<?php

namespace Facebook\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once('vendor/autoload.php');

$host = 'http://localhost:4444/';
$capabilities = DesiredCapabilities::chrome();

$driver = RemoteWebDriver::create($host, $capabilities);


$response = $driver->get('https://www.autotrader.co.uk/retailer/stock?advertising-location=at_motorhomes&advertising-location=at_profile_motorhomes&channel=motorhomes&dealer=10025576&sort=desirability-desc');

$csvFile = fopen('autotrader.csv', 'w');

// Write the CSV header
fputcsv($csvFile, [
    'Year',
    'Mileage',
    'Make',
    'Model',
    'Subtitle',
    'Vehicle Images',
    'Regular price',
    'Vehicle Overview',
    'General Information',
    'Video Link',
    'Exterior Colour',
    'Transmission',
    'Engine',
    'Fuel Type',
    'Berth',
    'Belted Seats',
    'Location'
]);

// $tableElement = $driver->findElement(WebDriverBy::id('dealerStockResultsTable'));
$tableElement = $driver->findElement(WebDriverBy::className('at-search-results__list'));

try {
    // Wait until the presence of the h2 element is located
    $driver->wait()->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('aside.dealer_stock_search h2'))
    );

    // Find the h2 element within the aside with class "dealer_stock_search"
    $h2Element = $driver->findElement(WebDriverBy::cssSelector('aside.dealer_stock_search h2'));

    // Get the text content of the h2 element
    $h2Text = $h2Element->getText();

    // Use a regular expression to extract the count
    preg_match('/(\d+) Motorhomes found/', $h2Text, $matches);

    // Convert the matched value to an integer
    $count = isset($matches[1]) ? (int)$matches[1] : 0;

    // Output the count
    // echo "Count: $count\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
// die;

for ($i = 1; $i <= $count; $i++) {

    $xpath = '/html/body/div[3]/main/div/div/div[2]/div[2]/section/div[2]/ul/div/div/li[' . $i . ']/a';

    // Use WebDriverBy::xpath() to create a WebDriverBy object
    $byXPath = WebDriverBy::xpath($xpath);
    $driver->executeScript('window.scrollTo(0, document.body.scrollHeight);');

    // Wait for the element to be clickable
    $driver->wait()->until(
        WebDriverExpectedCondition::visibilityOfElementLocated($byXPath)
    );

    // Find the element and click it
    $atag = $driver->findElement($byXPath);
    $atag->click();

    $driver->wait()->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('button[data-testid="gallery-view-more-button"]'))
    );

    // Find the button by its data-testid attribute and click it
    $button = $driver->findElement(WebDriverBy::cssSelector('button[data-testid="gallery-view-more-button"]'));
    $button->click();

    $driver->wait()->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('label[data-testid="video-toggle"]'))
    );
    sleep(5);
    // Find the label element by its data-testid attribute and click it
    $videoLabel = $driver->findElement(WebDriverBy::cssSelector('label[data-testid="video-toggle"]'));
    $videoLabel->click();

    $videoComponent = $driver->findElement(WebDriverBy::cssSelector('div[data-testid="video-component"]'));

    // Find the iframe element within the div
    $iframeElement = $videoComponent->findElement(WebDriverBy::tagName('iframe'));

    // Get the value of the src attribute
    $srcAttributeValue = $iframeElement->getAttribute('src');

    // Wait until the src attribute value is not empty
    $driver->wait()->until(function ($driver) use ($srcAttributeValue) {
        return !empty($srcAttributeValue);
    });

    // Output the src attribute value
    // echo "Video URL: $srcAttributeValue\n";
    // sleep(5);
    $driver->wait()->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('button[data-gui="gallery-close"]'))
    );

    $closeButton = $driver->findElement(WebDriverBy::cssSelector('button[data-gui="gallery-close"]'));

    // Click the "Close" button
    $closeButton->click();
    // die;
    $sectionElement = $driver->findElement(WebDriverBy::className('sc-beZgAy'));

    // Find the <img> element within the section
    $imgElements = $sectionElement->findElements(WebDriverBy::tagName('img'));

    // Get the value of the src attribute
    foreach ($imgElements as $imgElement) {
        $imgSrc = $imgElement->getAttribute('src');
        // echo "Image source: $imgSrc\n";
    }

    // Locate the section element with the specified class
    $sectionElement = $driver->findElement(WebDriverBy::className('ePGmhp'));

    // Find the element with class 'fjdNsg' within the section
    $textElementMain = $sectionElement->findElement(WebDriverBy::className('fjdNsg'));
    $textElementSub = $sectionElement->findElement(WebDriverBy::className('iTTKTa'));
    $register = $sectionElement->findElement(WebDriverBy::className('haMTsh'));

    try {
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('sc-cYxjnA'))
        );

        // Find the element by class name
        $priceElement = $driver->findElement(WebDriverBy::className('sc-cYxjnA'));

        // Get the text content of the element
        $priceText = $priceElement->getText();

        // Extract only numeric characters from the string
        preg_match_all('/\d+/', $priceText, $matches);
        $numericPrice = implode('', $matches[0]);

        // Output the extracted numeric price
        // echo "Numeric Price: $numericPrice\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }



    // Get the text content of the element
    $textContentMain = $textElementMain->getText();
    $words = explode(' ', $textContentMain);
    // The first word is the Make
    $make = isset($words[0]) ? $words[0] : '';
    // The rest of the words are part of the Model
    $model = implode(' ', array_slice($words, 1));
    $textContentSub = $textElementSub->getText();
    // echo $textContentSub;die;
    $textContentRegister = $register->getText();
    $yearValue = preg_match('/(\d{4})/', $textContentRegister, $matches) ? $matches[1] : null;
    // $textContentPrice = $price->getText();

    $element = $driver->findElement(WebDriverBy::className('iEVJtK'));

    // Find the child element with class 'eXiUCy'
    $mileageElement = $element->findElement(WebDriverBy::className('eXiUCy'));

    // Get the text content of the element
    $mileageText = $mileageElement->getText();

    $mileageNumeric = preg_replace('/[^0-9]/', '', $mileageText);

    // Output the numeric mileage
    // echo "Numeric Mileage: $mileageNumeric\n";

    $keySpecsSection = $driver->findElement(WebDriverBy::className('sc-fHsjty'));

    // Find the child element with class 'sc-OIPhM' containing 'Berth'
    $berthElement = $driver->findElement(WebDriverBy::cssSelector('span[data-testid="details"][class="sc-kAyceB sc-jhJOaJ cldNjY elacUr"]'));

    // Get the text content of the "Berth" element
    $berth = $berthElement->getText();

    // Output the result
    // echo "Berth: $berth\n";

    // Find the following sibling span element with class 'sc-jhJOaJ'
    $gearboxElement = $keySpecsSection->findElement(
        WebDriverBy::xpath(".//span[@class='sc-kAyceB sc-OIPhM cldNjY jhovwP' and text()='Gearbox']")
    );

    // Find the following sibling span element with class 'sc-jhJOaJ'
    $gearboxValueElement = $gearboxElement->findElement(WebDriverBy::xpath('./following-sibling::span[@class="sc-kAyceB sc-jhJOaJ cldNjY elacUr"]'));

    // Get the text content of the 'Gearbox' value element
    $gearboxValue = $gearboxValueElement->getText();

    // Output the 'Gearbox' value
    // echo "Gearbox Value: $gearboxValue\n";

    $engineSizePowerElement = $keySpecsSection->findElement(
        WebDriverBy::xpath(".//span[@class='sc-kAyceB sc-OIPhM cldNjY jhovwP' and text()='Engine size power']")
    );

    // Find the following sibling span element with class 'sc-jhJOaJ'
    $engineSizePowerValueElement = $engineSizePowerElement->findElement(
        WebDriverBy::xpath('./following-sibling::span[@class="sc-kAyceB sc-jhJOaJ cldNjY elacUr"]')
    );

    // Get the text content of the 'Engine size power' value element
    $engineSizePowerValue = $engineSizePowerValueElement->getText();

    // Output the 'Engine size power' value
    // echo "Engine Size Power Value: $engineSizePowerValue\n";

    $seatsElement = $keySpecsSection->findElement(
        WebDriverBy::xpath(".//span[@class='sc-kAyceB sc-OIPhM cldNjY jhovwP' and text()='Seats']")
    );

    // Find the following sibling span element with class 'sc-jhJOaJ'
    $seatsValueElement = $seatsElement->findElement(
        WebDriverBy::xpath('./following-sibling::span[@class="sc-kAyceB sc-jhJOaJ cldNjY elacUr"]')
    );

    // Get the text content of the 'Seats' value element
    $seatsValue = $seatsValueElement->getText();

    // Output the 'Seats' value
    // echo "Seats Value: $seatsValue\n";
    $driver->wait()->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.sc-fwwElh.sc-fSjEuY.hbMGzw.bPZAzG'))
    );

    // Find the button by CSS selector and click it
    $button = $driver->findElement(WebDriverBy::cssSelector('.sc-fwwElh.sc-fSjEuY.hbMGzw.bPZAzG'));
    $button->click();

    $driver->wait()->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.sc-kAyceB.sc-iLsKjm.cldNjY.iEtnIU'))
    );

    // Find and extract the "Fuel type" value specifically
    $fuelTypeElement = $driver->findElement(WebDriverBy::xpath('//dt[text()="Fuel type:"]/following-sibling::dd'));
    $fuelTypeValue = $fuelTypeElement->getText();

    // Print the extracted value
    // echo "Fuel type: $fuelTypeValue\n";
    try {
        $driver->wait()->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//dt[text()="Colour:"]/following-sibling::dd'))
        );
        $colourElement = $driver->findElement(WebDriverBy::xpath('//dt[text()="Colour:"]/following-sibling::dd'));
        $colourValue = $colourElement->getText();
    } catch (\Throwable $th) {
    }


    // Print the extracted value
    // echo "Colour: $colourValue\n";
    $chassisMakeElement = $driver->findElement(WebDriverBy::xpath('//dt[text()="Chassis make:"]/following-sibling::dd'));
    $chassisMakeValue = $chassisMakeElement->getText();

    // Print the extracted "Chassis make" value
    // echo "Chassis make: $chassisMakeValue\n";
    $chassisModelElement = $driver->findElement(WebDriverBy::xpath('//dt[text()="Chassis model:"]/following-sibling::dd'));
    $chassisModelValue = $chassisModelElement->getText();

    // Print the extracted "Chassis model" value
    // echo "Chassis model: $chassisModelValue\n";
    $driver->wait()->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.sc-jlGgGc.eowcDf.atds-modal--close.atds-type-fiesta'))
    );

    sleep(5);
    $backButton = $driver->findElement(WebDriverBy::cssSelector('.sc-jlGgGc.eowcDf.atds-modal--close.atds-type-fiesta'));
    $backButton->click();

    $readMoreLink = $driver->findElement(WebDriverBy::cssSelector('a[data-gui="db-read-more"]'));
    $readMoreLink->click();

    // Wait until the new content is loaded (assuming a specific element appears)
    // $driver->wait()->until(
    //     WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.some-new-content'))
    // );

    $driver->wait()->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('.sc-jXbUNg.sc-gytJtb.fJiHvq.eGdxqk'))
    );

    // Find the first paragraph element
    $firstParagraph = $driver->findElement(WebDriverBy::cssSelector('.sc-jXbUNg.sc-gytJtb.fJiHvq.eGdxqk'));
    $firstParagraphText = $firstParagraph->getText();

    // Wait until the presence of the <p> element is located
    $pTextContent = '';
    $ulText = '';

    // Find all elements with the specified class
    $extraFeaturesDivs = $driver->findElements(WebDriverBy::className('sc-dqYEFG'));

    // Check if the element exists
    if (!empty($extraFeaturesDivs)) {
        $extraFeaturesDiv = $extraFeaturesDivs[0]; // Assuming there is only one element, you can modify this based on your HTML structure

        // Find the child p element within the div
        $pElements = $extraFeaturesDiv->findElements(WebDriverBy::tagName('p'));

        // Check if the p element exists
        if (!empty($pElements)) {
            $pTextContent = $pElements[0]->getText();
        }

        // Find the <ul> element within the div
        $ulElements = $extraFeaturesDiv->findElements(WebDriverBy::cssSelector('.sc-kaaGRQ.klkKyW'));

        // Check if the ul element exists
        if (!empty($ulElements)) {
            // Get the text content of the <ul> element
            $ulText = $ulElements[0]->getText();

            // Split the text content into an array of lines
            $ulLines = explode("\n", $ulText);

            // Remove any empty lines
            $ulLines = array_filter($ulLines, 'strlen');

            // Combine the lines into a single string, separated by commas
            $ulText = implode(', ', $ulLines);
        }
    }

    // Output the text content of the <ul> element
    // echo $ulText;die;

    // Find and click the "Back" button
    $driver->wait()->until(
        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.sc-jlGgGc.eowcDf.atds-modal--close.atds-type-fiesta'))
    );
    $backButton1 = $driver->findElement(WebDriverBy::cssSelector('.sc-jlGgGc.eowcDf.atds-modal--close.atds-type-fiesta'));
    $backButton1->click();

    // Output the text content of the first <p> tag
    // echo $firstParagraphText;

    preg_match('/(?:Vehicle\s*Location:|Location:)\s*([\w\s,]+)\./i', $firstParagraphText, $matches);
    $locationValue = isset($matches[1]) ? trim($matches[1]) : '';

    // Output the location value
    // echo $locationValue;die;

    $year =  $yearValue  ?? '';
    $mileage = $mileageNumeric ?? '';
    $make = $make ?? '';
    $model = $model ?? '';
    $subtitle = $textContentSub ?? '';
    $vehicleImages = $imgSrc ?? '';
    $regularPrice = $numericPrice ?? '';
    $vehicleOverview = $firstParagraphText ?? '';
    $generalInformation = $pTextContent . $ulText ?? '';
    $videoLink = $srcAttributeValue ?? '';
    $exteriorColour = $colourValue ?? '';
    $transmission = $gearboxValue ?? '';
    $engine = $engineSizePowerValue ?? '';
    $fuelType = $fuelTypeValue ?? '';
    $berth = $berth ?? '';
    $beltedSeats = $seatsValue ?? '';
    $location = $locationValue ?? '';


    fputcsv($csvFile, [
        $year,
        $mileage,
        $make,
        $model,
        $subtitle,
        $vehicleImages,
        $regularPrice,
        $vehicleOverview,
        $generalInformation,
        $videoLink,
        $exteriorColour,
        $transmission,
        $engine,
        $fuelType,
        $berth,
        $beltedSeats,
        $location
    ]);

    sleep(2);

    $driver->get('https://www.autotrader.co.uk/retailer/stock?advertising-location=at_motorhomes&advertising-location=at_profile_motorhomes&channel=motorhomes&dealer=10025576&sort=desirability-desc');
}
fclose($csvFile);

$driver->quit();
