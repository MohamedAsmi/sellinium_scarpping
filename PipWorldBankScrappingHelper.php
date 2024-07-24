<?php

require_once('vendor/autoload.php');
require_once('database.php');
require_once('SeleniumHandler.php'); // Include your common functions file

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class PipWorldBankScrappingHelper
{
    private $driver;
    private $seleniumFunctions;
    private const TABLENAME = 'pip_worldbank_records';
    
    public function __construct($webDriverHost)
    {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($webDriverHost, $capabilities);
        $this->seleniumFunctions = new SeleniumHandler($this->driver);
        
    }

    public function runScript($url)
    {
        try {
            $this->seleniumFunctions->maximizeWindow();
            $this->seleniumFunctions->navigateToUrl($url);
            $this->seleniumFunctions->waitUntilOptionVisible('css', 'div[joyridestep="thirdStep"][stepposition="top"]');

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
        $povertyLists = $this->seleniumFunctions->findSubElements('css', 'div[joyridestep="thirdStep"][stepposition="top"]', 'tag', 'ul');
        $this->processPovertyLists($povertyLists);
    }

    private function processPovertyLists($povertyLists)
    {
        foreach ($povertyLists as $povertyList) {
            $povertyItems = $this->seleniumFunctions->findSubElementsWithParent($povertyList,'tag', 'li');
            foreach ($povertyItems as $povertyItem) {
                $this->processPovertyItem($povertyItem);
                $this->clickAndListCountryDropdowns();
            }
            echo "-----------------------------------\n"; // Separator between <ul> elements
        }
    }

    private function processPovertyItem($povertyItem)
    {
        try {
            $povertyLabel = $this->seleniumFunctions->subCssElementSelector($povertyItem, 'label');
            $povertyLabelText = $povertyItem->getText();
            $povertyLabel->click();
            echo "Clicked on label: $povertyLabelText\n";
        } catch (Exception $e) {
            echo "Error clicking on label: " . $e->getMessage() . "\n";
        }
    }

    private function clickAndListCountryDropdowns()
    {
        for ($x = 0; $x < 2; $x++) {
            $this->seleniumFunctions->findAndClickElement('css', '.k-dropdown-wrap');
            $count = $this->pickPovertyCountryNameOneByOne($x, true);
            echo "Count of Country -- " . $count;
            for ($i = 0; $i < 100; $i++) {
                $this->pickPovertyCountryNameOneByOne($i);
                $this->clickTableTabButton();
                $this->scrapAndStorePovertyTableData();
            }
        }
    }

    private function pickPovertyCountryNameOneByOne($index, $isCount = false)
    {
        try {
            $countryDropdown = $this->seleniumFunctions->findElement('css', 'div[joyridestep="firstStep"]');
            $countrySelector = $this->seleniumFunctions->findAndClickSubElementsWithParent($countryDropdown,'css','.k-widget.k-multiselect');
          
         

            $dropdownContainer = $this->seleniumFunctions->findElement('css','.k-animation-container');
            $options = $this->seleniumFunctions->findSubElementsWithParent($dropdownContainer,'css','ul li');
            $this->RemovePovertySelectedCountyName();

            echo "Count option ----" . count($options);
            echo "Option: " . $options[$index]->getText() . "\n";
            $options[$index]->click();

            if ($isCount) {
                return count($options);
            }

            sleep(1);
        } catch (Exception $e) {
            echo "Error selecting country from list: " . $e->getMessage() . "\n";
        }
    }



    private function removePovertySelectedCountyName()
    {
        try {
            $parentUlElement = $this->seleniumFunctions->findElement('css', 'ul.k-reset');
            $this->seleniumFunctions->findAndClickSubliElements('css', 'li[role="option"]', $parentUlElement, 'css', '.k-icon.k-i-close');
            echo "Clicked on the k-i-close button.\n";
        } catch (Exception $e) {
            echo "Error clicking the k-i-close button: " . $e->getMessage() . "\n";
        }
    }


    private function clickTableTabButton()
    {
        $this->seleniumFunctions->findAndClickElement('id', 'table-tab');
    }

    private function scrapAndStorePovertyTableData()
    {
        try {
           $tbodyElements = $this->seleniumFunctions->findSubElements('css','table.k-grid-table','tag','tbody');
            $table =  $this->seleniumFunctions->findElement('css','table.k-grid-table');
            $tbodyElements = $table->findElements(WebDriverBy::tagName('tbody'));

            foreach ($tbodyElements as $tbody) {
                $rows = $tbody->findElements(WebDriverBy::tagName('tr'));
                foreach ($rows as $row) {
                    $TableRowData = $this->getPovertyTableRowData($row);
                    $this->storeDataInDatabase($TableRowData);
                }
            }
        } catch (Exception $e) {
            echo "Error fetching table data: " . $e->getMessage() . "\n";
        }
    }

    private function getPovertyTableRowData($row)
    {
        $cells = $row->findElements(WebDriverBy::tagName('td'));
        $rowData = [];
        foreach ($cells as $cell) {
            $rowData[] = $cell->getText();
        }

        return [
            'year' => $rowData[0],
            'region' => $rowData[1],
            'level' => $rowData[2],
            'poverty_rate' => $rowData[3],
            'poverty_line' => $rowData[4],
            'interpolated' => $rowData[5],
            'datatype' => $rowData[6]
        ];
    }
 
    private function storeDataInDatabase($data)
    {
        $db = new Database();
        $db->insertData(self::TABLENAME,$data);
    }
}

?>
