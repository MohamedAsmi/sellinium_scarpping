<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

class SeleniumHandler
{
    private $driver;

    // Selector type constants
    private const SELECTOR_ID = 'id';
    private const SELECTOR_CSS = 'css';
    private const SELECTOR_TAG = 'tag';
    private const SELECTOR_CLASS = 'class';
    private const SELECTOR_XPATH = 'xpath';

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    private function getElement($type, $selector)
    {
        switch($type) {
            case self::SELECTOR_CSS:
                return WebDriverBy::cssSelector($selector);
            case self::SELECTOR_ID:
                return WebDriverBy::id($selector);
            case self::SELECTOR_TAG:
                return WebDriverBy::tagName($selector);
            case self::SELECTOR_CLASS:
                return WebDriverBy::className($selector);
            default:
                throw new Exception("Invalid selector type: $type");
        }
    }
    public function maximizeWindow()
    {
        try {
            $this->driver->manage()->window()->maximize();
        } catch (Exception $th) {
            echo "ERROR IN MAXZIMIZE WINDOW : " . $th;
        }
    }

    public function navigateToUrl($url)
    {
        try {
            $this->driver->get($url);
        } catch (Exception $th) {
            echo "ERROR IN NAVIGATE TO URL: " . $th;
        }
    }

    public function waitUntilOptionVisible($type, $selector, $timeout = 10)
    {
        try {
            $this->driver->wait($timeout)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated($this->getElement($type, $selector))
            );
        } catch (Exception $th) {
            echo "ERROR WAIT UNTIL OPTION VISIBLE TYPE = " . $type . " SELECTOR = " . $selector . " ERROR: " . $th;
        }
    }

    public function waitUntilElementToBeClickable($type, $selector, $timeout = 10)
    {
        try {
            $this->driver->wait($timeout)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated($this->getElement($type, $selector))
            );
        } catch (Exception $th) {
            echo "ERROR WAIT UNTIL OPTION VISIBLE TYPE = " . $type . " SELECTOR = " . $selector . " ERROR: " . $th;
        }
    }

    public function findElement($type, $selector)
    {
        try {
            return $this->driver->findElement($this->getElement($type, $selector));
        } catch (Exception $th) {
            echo "ELEMENT NOT FOUND: TYPE = " . $type . " SELECTOR = " . $selector . " ERROR: " . $th;
            return null;
        }
    }
    public function findAndClickElement($type, $selector)
{
    try {
        // Log the types and values of the parameters
        echo "findAndClickElement called with TYPE = " . $type . " SELECTOR = " . (is_object($selector) ? get_class($selector) : $selector) . "\n";

        // Wait for the element to be visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($this->getElement($type, $selector))
        );
        // Wait for the element to be clickable
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::waitUntilElementToBeClickable($this->getElement($type, $selector))
        );
        // Find the element freshly before every click
        $element = $this->driver->findElement($this->getElement($type, $selector));
        // Scroll the element into view
        $this->driver->executeScript("arguments[0].scrollIntoView(true);", [$element]);
        echo "Clickable Value = " . $element->getText();
        // Use JavaScript to click the element
        $this->driver->executeScript("arguments[0].click();", [$element]);
        sleep(5);
    } catch (Exception $th) {
        // Ensure selector is not an object in the error message
        $selectorText = is_object($selector) ? "Object" : $selector;
        echo "ELEMENT NOT FOUND: TYPE = " . $type . " SELECTOR = " . $selectorText . " ERROR: " . $th->getMessage();
        return null;
    }
}

public function findAndClickElementWithParent($parent, $type, $selector)
{
    try {
        // Wait for the parent element to be visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOf($parent)
        );

        // Wait for the child element within the parent to be visible
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($this->getElement($type, $selector))
        );

        // Wait for the child element within the parent to be clickable
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::waitUntilElementToBeClickable($this->getElement($type, $selector))
        );

        // Find the child element within the parent
        $element = $parent->findElement($this->getElement($type, $selector));

        // Log the clickable value
        echo "Clickable Value = " . $element->getText();

        // Scroll the element into view
        $this->driver->executeScript("arguments[0].scrollIntoView(true);", [$element]);

        // Use JavaScript to click the element
        $this->driver->executeScript("arguments[0].click();", [$element]);

        // Wait for some time to ensure the click is processed
        sleep(5);

        return true;
    } catch (Exception $th) {
        // Log detailed error information
        print_r("---------------------------\n");
        print_r("ELEMENT NOT FOUND: TYPE =  " . $type . "\n");
        print_r(" SELECTOR = " . (is_object($selector) ? get_class($selector) : $selector) . "\n");
        print_r(" ERROR OCCURRED IN FIND AND CLICK ELEMENT: " . $th->getMessage() . "\n");
        print_r("---------------------------\n");
        return null;
    }
}

    public function findElements($type, $selector)
    {
        try {
            return $this->driver->findElements($this->getElement($type, $selector));
        } catch (Exception $th) {
            echo "ELEMENTS NOT FOUND: TYPE = " . $type . " SELECTOR = " . $selector . " ERROR: " . $th;
            return [];
        }
    }

    // public function findAndClickElement($type, $selector)
    // {
    //     try {
    //         $selector = $this->driver->findElement($this->getElement($type, $selector));
    //         $selector->click();
    //         sleep(5);
    //     } catch (Exception $th) {
    //         echo "ERROR IN FIND AND CLICK ELEMENT : TYPE = " . $type . " SELECTOR = " . $selector . " ERROR: " . $th;
    //     }
    // }

    public function findSubElement($type, $selector, $stype, $subselector)
    {
        try {
            $parent = $this->driver->findElement($this->getElement($type, $selector));
            return $parent->findElement($this->getElement($stype, $subselector));
        } catch (Exception $th) {
            echo "SUBELEMENT NOT FOUND: PARENT TYPE = " . $type . " PARENT SELECTOR = " . $selector . " SUBSELECTOR TYPE = " . $stype . " SUBSELECTOR = " . $subselector . " ERROR: " . $th;
            return null;
        }
    }

    public function findAndClickSubElement($type, $selector, $stype, $subselector)
    {
        try {
            $parent = $this->driver->findElement($this->getElement($type, $selector));
            $parent->click();
        } catch (Exception $th) {
            echo "SUBELEMENT NOT FOUND: PARENT TYPE = " . $type . " PARENT SELECTOR = " . $selector . " SUBSELECTOR TYPE = " . $stype . " SUBSELECTOR = " . $subselector . " ERROR: " . $th;
            return null;
        }
    }

    public function findSubElements($type, $selector, $stype, $subselector)
    {
        try {
            $parent = $this->driver->findElement($this->getElement($type, $selector));
            return $parent->findElements($this->getElement($stype, $subselector));
        } catch (Exception $th) {
            echo "SUBELEMENTS NOT FOUND: PARENT TYPE = " . $type . " PARENT SELECTOR = " . $selector . " SUBSELECTOR TYPE = " . $stype . " SUBSELECTOR = " . $subselector . " ERROR: " . $th;
            return [];
        }
    }
    public function findSubElementWithParent($type, $subselector, $parent) {
        try {
            return $parent->findElement($this->getElement($type, $subselector));
        } catch (Exception $th) {
            echo "SUBELEMENT NOT FOUND: TYPE = " . $type . " SUBSELECTOR = " . $subselector . " ERROR: " . $th;
            return null;
        }
    }

    public function findSubElementsWithParent($parent,$type, $subselector)
    {
        try {
            return $parent->findElements($this->getElement($type, $subselector));
        } catch (Exception $th) {
            echo "SUBELEMENTS WITH PARENT NOT FOUND: TYPE = " . $type . " SUBSELECTOR = " . $subselector . " ERROR: " . $th;
            return [];
        }
    }
    public function findClickSubElementsWithParent($parent,$type, $subselector)
    {
        try {
            $element = $parent->findElements($this->getElement($type, $subselector));
            $element->click();
        } catch (Exception $th) {
            echo "SUBELEMENTS WITH PARENT NOT FOUND: TYPE = " . $type . " SUBSELECTOR = " . $subselector . " ERROR: " . $th;
            return [];
        }
    }
    public function findAndClickSubElementsWithParent($parent, $type, $subselector)
    {
        try {
            $children = $parent->findElements($this->getElement($type, $subselector));
            
            foreach ($children as $child) {
                $child->click();
                // sleep(1); // Add sleep if needed between clicks
            }
            
            return $children; // Return array of clicked elements if needed
        } catch (Exception $th) {
            echo "SUBELEMENTS WITH PARENT NOT FOUND: TYPE = " . $type . " SUBSELECTOR = " . $subselector . " ERROR: " . $th->getMessage();
            return [];
        }
    }
    public function findAndClickSubElementWithParent($parent, $type, $subselector)
    {
        try {
            return$parent->findElement($this->getElement($type, $subselector))->click();
        } catch (Exception $th) {
            echo "SUBELEMENTS WITH PARENT NOT FOUND: TYPE = " . $type . " SUBSELECTOR = " . $subselector . " ERROR: " . $th->getMessage();
            return [];
        }
    }


    public function clickAllDropdownOptions($dropdownButtonSelector, $optionsContainerSelector, $optionSelector)
{
    try {
    // Click the dropdown button to open the options
    $dropdownButton = $this->driver->findElement(WebDriverBy::cssSelector($dropdownButtonSelector));
    $dropdownButton->click();
    
    // Wait until the options are present
    $this->driver->wait()->until(
        WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::cssSelector($optionsContainerSelector))
    );

    // Find all options in the dropdown
    $options = $this->driver->findElements(WebDriverBy::cssSelector($optionSelector));

    // Click each option
    foreach ($options as $option) {
        $optionText = $option->getText();
        echo "Clicking option: $optionText\n";
        $option->click();

        // Optionally, you can add some delay if needed
        usleep(500000); // sleep for 0.5 second

        // Reopen the dropdown after each click if necessary
        $dropdownButton->click();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::cssSelector($optionsContainerSelector))
        );
    }

    return true; // Indicate success
}  catch (Exception $e) {
    echo "ERROR OCCURRED: ERROR: " . $e->getMessage();
}
return false; // Ind // Indicate failure
}
public function clickInputInListItems($parentDiv)
{
    try {
        $liElements = $this->findSubElementsWithParent($parentDiv, self::SELECTOR_TAG, 'li');
        foreach($liElements as $liElement) {
            $inputElement = $liElement->findElement($this->getElement(self::SELECTOR_TAG, 'input'));
            
            // Scroll to the element
            $this->driver->executeScript("arguments[0].scrollIntoView(true);", [$inputElement]);
            
            // Wait until the element is visible
            $wait = new WebDriverWait($this->driver, 10);
            $wait->until(WebDriverExpectedCondition::visibilityOf($inputElement));
            
            // Click the element
            $inputElement->click();
    }
    }  catch (Exception $e) {
        echo "ERROR OCCURRED: ERROR: " . $e->getMessage();
    }
}
public function clickElements($elements)
{
    try {
        foreach ($elements as $element) {
            $element->click();
        }
    } catch (Exception $e) {
        echo "Error clicking elements: " . $e->getMessage() . "\n";
    }
}
    public function findAndClickSubliElements($type, $selector, $parentElement, $subType, $subSelector)
    {
        try {
            // Find the parent element
            $parent = $parentElement->findElement($this->getElement($type, $selector));
            
            // Find the child elements
            $children = $parent->findElements($this->getElement($subType, $subSelector));
            
            // Click on each child element
            foreach ($children as $child) {
                $child->click();
            }
            
            return $children;
        } catch (Exception $e) {
            echo "SUBELEMENTS NOT FOUND: PARENT TYPE = " . $type . " PARENT SELECTOR = " . $selector . " SUBSELECTOR TYPE = " . $subType . " SUBSELECTOR = " . $subSelector . " ERROR: " . $e->getMessage() . "\n";
            return [];
        }
    }

    public function clickSubElement($parent, $type, $selector)
    {
        try {
            $element = $parent->findElement($this->getElement($type, $selector));
            $element->click();
        } catch (Exception $e) {
            echo "Error clicking sub-element: " . $e->getMessage() . "\n";
        }
    }

    
    public function clickSubElements($parent, $type, $selector)
    {
        try {
            $elements = $parent->findElements($this->getElement($type, $selector));
            foreach($elements as $element){
                $element->click(); 
            }
        } catch (Exception $e) {
            echo "Error clicking sub-element: " . $e->getMessage() . "\n";
        }
    }

    // public function selectTableTab()
    // {
    //     try {
    //         $tableTab = $this->driver->findElement($this->getElement(self::SELECTOR_ID, 'table-tab'));
    //         $tableTab->click();
    //     } catch (Exception $th) {
    //         echo "ERROR IN SELECT TABLE TAB ERROR: " . $th;
    //     }
    // }

    public function cssElementSelector($selector)
    {
        try {
            return $this->driver->findElement($this->getElement(self::SELECTOR_CSS, $selector));
        } catch (Exception $th) {
            echo "CSS ELEMENT NOT FOUND: SELECTOR = " . $selector . " ERROR: " . $th;
            return null;
        }
    }

    public function subElementSelector($selector)
    {
        try {
            return $this->driver->findElement($this->getElement(self::SELECTOR_CSS, $selector));
        } catch (Exception $th) {
            echo "SUB ELEMENT NOT FOUND: SELECTOR = " . $selector . " ERROR: " . $th;
            return null;
        }
    }

    public function findTagName($parent, $element)
    {
        try {
            return $parent->findElements(WebDriverBy::tagName($element));
        } catch (Exception $th) {
            echo "TAG NAME ELEMENTS NOT FOUND: TAG = " . $element . " ERROR: " . $th;
            return [];
        }
    }

    public function subCssElementSelector($parent, $selector)
    {
        try {
            return $parent->findElement($this->getElement(self::SELECTOR_CSS, $selector));
        } catch (Exception $th) {
            echo "SUB CSS ELEMENT NOT FOUND: SELECTOR = " . $selector . " ERROR: " . $th;
            return null;
        }
    }


    public function getListItems($ulId)
    {
        try {
            $wait = new WebDriverWait($this->driver, 10); // Wait up to 10 seconds
            $ulElement = $wait->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id($ulId))
            );

            $liElements = $ulElement->findElements(WebDriverBy::tagName('li'));
            $items = [];

            foreach ($liElements as $li) {
                // Check if there's inner text or elements
                $items[] = $li->getText(); // Optionally add more checks here
            }

            return $items;
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage() . "\n";
            return [];
        }
    }

    public function getListItemsFromUl($ulElement)
    {
        try {
            $liElements = $ulElement->findElements(WebDriverBy::tagName('li'));
            $items = [];

            foreach ($liElements as $li) {
                $items[] = $li->getText();
            }

            return $items;
        } catch (NoSuchElementException $e) {
            echo "An error occurred: " . $e->getMessage() . "\n";
            return [];
        }
    }
    public function waitForPageToLoad($timeout = 10)
    {
        $this->driver->wait($timeout)->until(function ($driver) {
            return $driver->executeScript('return document.readyState') === 'complete';
        });
    }
}

?>
