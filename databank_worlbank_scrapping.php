<?php

require_once('DataBankWorldBank.php');
require_once('config.php');

// $url = 'https://pip.worldbank.org/poverty-calculator';

// Create an instance of SeleniumHandler
$seleniumHandler = new DataBankWorldBank(WEBDRIVER_HOST);

// Run the script using the SeleniumHandler instance
$seleniumHandler->runScript();


?>
