<?php

require_once('PipWorldBankScrappingHelper.php');
require_once('config.php');

// $url = 'https://pip.worldbank.org/poverty-calculator';

// Create an instance of SeleniumHandler
$seleniumHandler = new PipWorldBankScrappingHelper(WEBDRIVER_HOST);

// Run the script using the SeleniumHandler instance
$seleniumHandler->runScript(SITE_URL);


?>
