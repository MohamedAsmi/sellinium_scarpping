<?php

require_once('vendor/autoload.php');
require_once('database.php');
require_once('SeleniumHandler.php'); // Include your common functions file
// require_once('DataBankSubWorldBankScrappingHelper.php'); // Include your common functions file

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

class DataBankWorldBank{


    private $driver;

    private $seleniumHandle;
    private $db;
    private const TABLEDATABASE = 'database_worldbank_records';
    private const TABLECOUNTRY = 'countries_worldbank_records';
    private const TABLESERIES = 'series_worldbank_records';
    private const TABLETIME = 'times_worldbank_records';
    private const URL = 'https://databank.worldbank.org/source/statistical-performance-indicators-(spi)';
    
    public function __construct($webDriverHost)
    {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($webDriverHost, $capabilities);
        $this->seleniumHandle = new SeleniumHandler($this->driver);
        $this->db = new Database();
        
    }
    
    public function runScript()
    {
        try {
            $this->seleniumHandle->maximizeWindow();
            $this->seleniumHandle->navigateToUrl(self::URL);
            // $this->StoreDatabaseListToDb();
            $this->StoreCountryListToDb();
        } catch (Exception $e) {
            echo "An error occurred runScript: " . $e->getMessage() . "\n";
        } finally {
            $this->driver->quit();
        }
    }

    public function StoreDatabaseListToDb(){
        $this->clickDatabankDatabaseButton();
        sleep(10);
        $this->DatabaseList('id','tbl_DBList');
        
    }

    public function StoreCountryListToDb(){

        $getAllDatabaseRecord = $this->db->fetchAllData(self::TABLEDATABASE);
        for ($i=0; $i < count($getAllDatabaseRecord); $i++) { 
            $this->clickDatabankDatabaseButton();
            $this->PickDatabaseList('id','tbl_DBList',$i,$getAllDatabaseRecord[$i]['id']);
            // $this->clickDatabankDatabaseButton();
        }
    }



    public function countList($selector,$att){
        $parentDiv = $this->seleniumHandle->findElement($selector, $att);
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');
        return count($liElements);
    }

    public function DatabaseList($selector,$att){
        $parentDiv = $this->seleniumHandle->findElement($selector, $att);
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');
        
        foreach($liElements as $liElement){
            echo $liElement->getText();
       
            $data= $this->MappingDatabaseData([$liElement->getText()]);
            $this->storeData(self::TABLEDATABASE,$data);
        }
  
        return count($liElements);
    }

    public function PickDatabaseList($selector,$att,$i,$did){
        $parentDiv = $this->seleniumHandle->findElement($selector, $att);
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');
        $li = $liElements[$i];
        $item = $this->seleniumHandle->findAndClickElementWithParent($li,'css','input[type="radio"][name="databaseName"].radioSelectElement');
        
        sleep(2);
        $this->clickDatabankAcceptPopupWindow();
        $contriescount = $this->getCountriesCount($did);
        $seriescount = $this->getSeriesCount($did);
        $timescount = $this->getTimesCount($did);
        echo '<br>';
        echo 'contriescount ='. $contriescount;
        echo '<br>';
        echo 'seriescount ='. $seriescount;
        echo '<br>';
        echo 'timescount ='. $timescount;
        echo '<br>';
        sleep(10);
    }

    public function getCountriesCount($did){
    
        $parentDiv = $this->seleniumHandle->findElement('css', '.variableTable.availableView.table-dimension-C');
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');
        $data= $this->MappingData([$did,count($liElements)]);
        $this->storeData(self::TABLECOUNTRY,$data);
        return count($liElements);
    }

    public function getSeriesCount($did){

        $this->clickDatabankSeriesButton();
        $parentDiv = $this->seleniumHandle->findElement('css', '.variableTable.availableView.table-dimension-S');
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');
        $data= $this->MappingData([$did,count($liElements)]);
        $this->storeData(self::TABLESERIES,$data);
   
        return count($liElements);
    }
    public function getTimesCount($did){
        $this->clickDatabankTimeButton();
        $parentDiv = $this->seleniumHandle->findElement('css', '.variableTable.availableView.table-dimension-T');
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');
        $data= $this->MappingData([$did,count($liElements)]);
        $this->storeData(self::TABLETIME,$data);
        return count($liElements);
    }


    public function mapCountryList($selector,$att){
        $parentDiv = $this->seleniumHandle->findElement('id', $att);
        $liElements = $this->seleniumHandle->findSubElementsWithParent($parentDiv, 'tag', 'li');

        return count($liElements);
    }


    private function clickDatabankAcceptPopupWindow()
    {
        $this->driver->wait(WebDriverExpectedCondition::alertIsPresent());
        $alert = $this->driver->switchTo()->alert();
        $alert->accept();
        sleep(5);
    }


    private function clickDatabankDatabaseButton()
    {
        return $this->seleniumHandle->findAndClickElement('id', 'databaseLink');
    }
    
    private function clickDatabankSeriesButton()
    {
        return $this->seleniumHandle->findAndClickElement('css', 'a[title="Series"]');
    }

    private function clickDatabankTimeButton()
    {
        return $this->seleniumHandle->findAndClickElement('css', 'a[title="Time"]');
    }
    
    private function clickDatabankButton($selectvalue, $variable, $maxRetries = 4)
    {
        $maxRetries = 3; // Number of retry attempts.
        echo "<br>";
        echo "selected Value = ".$selectvalue;
        echo "<br>";
        echo "<br>";
        echo "selected Variable = ".$variable;
        echo "<br>";
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                echo "Attempt $attempt to click links.\n";

                $wait = new WebDriverWait($this->driver, 10); // 10 seconds timeout

                $aElements = $wait->until(
                    WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                        WebDriverBy::cssSelector('.tab-content a[data-parent="#nonMetadataReportVariables"]')
                    )
                );

                foreach ($aElements as $key => $aElement) {
                    if ($key == $selectvalue && $variable == "database") {
                        $aElement->click();
                        echo "Clicked on database link.\n";
                    } elseif ($key == $selectvalue && $variable == "country") {
                        $aElement->click();
                        echo "Clicked on country link.\n";
                    } elseif ($key == $selectvalue && $variable == "series") {
                        $aElement->click();
                        echo "Clicked on series link.\n";
                    } elseif ($key == $selectvalue && $variable == "time") {
                        $aElement->click();
                        echo "Clicked on time link.\n";
                    }
                }
                sleep(10);

                return;

            } catch (NoSuchElementException $e) {
                echo "Links with data-parent='#nonMetadataReportVariables' not found: " . $e->getMessage() . "\n";
                echo $this->driver->getPageSource();
            } catch (Exception $e) {
                echo "An error occurred while clicking on links: " . $e->getMessage() . "\n";
            }

            if ($attempt < $maxRetries) {
                echo "Retry in 5 seconds...\n";
                sleep(5);
            }
        }
    }
    private function storeData($table,$data)
    {
        $this->db->insertOrUpdateData($table,$data,$data);
    }

    private function MappingDatabaseData($data)
    {
        return [
            'name' => $data[0],
        ];
    }

     private function MappingData($data)
    {
        return [
            'did' => $data[0],
            'count' => $data[1]
        ];
    }
}

