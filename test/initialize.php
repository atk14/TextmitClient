<?php
define("PRODUCTION",false);
define("DEVELOPMENT",false);
define("TEST",true);

define("TEXTMIT_DEFAULT_LANGUAGE","en");

require(__DIR__ . "/../vendor/autoload.php");

require( __DIR__ . "/page_component.php" );
require( __DIR__ . "/testing_api_data_fetcher.php" );
