<?php

require_once('vendor/autoload.php');
require_once('database.php');
require_once('SeleniumHandler.php'); // Include your common functions file

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class WashWorldBankScrappingHelper
{
    private $driver;
    private $is_check;
    private $seleniumHandle;
    private const TABLENAME = 'washdata_worldbank_records';
    private const URL = 'https://washdata.org/data/household#!/table?geo0=country&geo1=GHA';
    
    
    public function __construct($webDriverHost)
    {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($webDriverHost, $capabilities);
        $this->seleniumHandle = new SeleniumHandler($this->driver);
        $this->is_check = false;
    }
    public function findAndClickGroupElement()
    {
        try {
        $regionType =$this->seleniumHandle->findAndClickElement('css', '#geography-level-1 > button');
        echo "Clicked on Region Type button" . PHP_EOL;
        $checkboxElements = $this->seleniumHandle->findElements('css','.multiselect-dropdown-list a[role="menuitem"]');

        $groupLabels = $this->driver->findElements(WebDriverBy::cssSelector('.group-label.ng-binding'));

        // Output the text content of each found element
        foreach ($groupLabels as $key=>$label) {
            // Output the text content of each label
            if($key > 9)break;
            echo $label->getText() . "\n";
            try {
                // Attempt to click the span element
                $span = $label->findElement(WebDriverBy::tagName('span'));
                $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$span]);
                $this->driver->executeScript('arguments[0].click();', [$span]);
                sleep(2);
            } catch (Exception $e) {
                // Handle any errors or exceptions that occur during clicking
                echo 'Error clicking span: ' . $e->getMessage() . "\n";
            }
        }
        $this->clickAndListOfYearsDropdowns();
        sleep(80);
        $this->scrapAndStoreWashDataTableData();
        die;
           
           
        } catch (Exception $e) {
            echo "ERROR OCCURRED: ERROR: " . $e->getMessage();
        }
        return false; // Indicate failure
    }
    public function runScript()
    {
        try {
            $this->seleniumHandle->maximizeWindow();
            $this->seleniumHandle->navigateToUrl(self::URL);
            $this->seleniumHandle->waitUntilElementToBeClickable('css', '#geography-level-0 > button');

            sleep(10);
            $this->startScrapingProcess();
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage() . "\n";
        } finally {
            $this->driver->quit();
        }
    }

    private function startScrapingProcess()
    {
        $this->clickAndListCountryDropdowns();
    }

    private function clickAndListCountryDropdowns()
    {
        $geographyLink = $this->seleniumHandle->findAndClickElement('css','#geography-level-0 > button');
        echo "Clicked on Geography link" . PHP_EOL;
        $dropdownLists =$this->seleniumHandle->findSubElements('css','.multiselect-dropdown-list','css','.dropdown-option a');
        echo "Geography options:" . PHP_EOL;

        foreach ($dropdownLists as $key => $option) {
            if ($key == 0) continue;
            // if ($key == 1) continue;
            if ($key == 2) {
              $this->findAndClickGroupElement();
              break;
            }
            // die;
            // $value = $option->getText();
            // echo " - " . $value . PHP_EOL;
            // $option->click();
            // sleep(1);
            // $this->clickAndListOfRigionTypeDropdowns();
        }
    }

    private function clickAndListOfYearsDropdowns(){
        if($this->is_check === false){
            $regionType =$this->seleniumHandle->findAndClickElement('css', '#time-period-multi > button');
            echo "Clicked on Years Type button" . PHP_EOL;
            $yearsUlElements =$this->seleniumHandle->findSubElements('css', '#time-period-multi > div','css','.multiselect-dropdown-list .dropdown-option');
           foreach($yearsUlElements as $key=>$yearsUlElement){
                $count = count($yearsUlElements) - 1;
                if($key == $count)continue;
                $this->seleniumHandle->findAndClickSubElementWithParent($yearsUlElement,'css','a.ng-binding');
               
           }
           $this->is_check = true;
        }
       
   
    }
   
    private function scrapAndStoreWashDataTableData()
    {
        try {

            $rows = $this->seleniumHandle->findElements('css','tr[ng-repeat="record in tableData"]');

   
        foreach ($rows as $key=>$row) {
            if ($key > 10) {
               break;
            }
            
            $cells = $row->findElements(WebDriverBy::tagName('td'));
            $recordData = [];

            foreach ($cells as $cell) {
                $recordData[] = $cell->getText();
            }
        $TableRowData = $this->getPovertyTableRowData($recordData);
        $this->storeDataInDatabase($TableRowData);
    }

       
        } catch (Exception $e) {
            echo "Error fetching table data: " . $e->getMessage() . "\n";
        }
    }

    private function getPovertyTableRowData($row)
    {
        return [
            'type' => $row[0],
            'region' => $row[1],
            'residence_type' => $row[2],
            'service_type' => $row[3],
            'year' => $row[4],
            'coverage' => $row[5],
            'datatype' => $row[6],
            'service_lavel' => $row[6]
        ];
    }
 
    private function storeDataInDatabase($data)
    {
        $db = new Database();
        $db->insertData(self::TABLENAME,$data);
    }
}

?>
