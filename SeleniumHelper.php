<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SeleniumHelper
{
    private $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function waitForElement(WebDriverBy $selector, $timeout = 10)
    {
        $this->driver->wait($timeout)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($selector)
        );
    }

    public function clickElement(WebDriverBy $selector)
    {
        $element = $this->driver->findElement($selector);
        $element->click();
    }

    public function findElements(WebDriverBy $selector)
    {
        return $this->driver->findElements($selector);
    }
}

?>
