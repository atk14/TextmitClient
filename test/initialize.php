<?php
define("PRODUCTION",false);
define("DEVELOPMENT",false);
define("TEST",true);

define("TEXTMIT_DEFAULT_LANGUAGE","en");

require( __DIR__ . "/../src/textmit.php" );
require( __DIR__ . "/../src/textmit_result.php" );
require( __DIR__ . "/../src/fulltext_data.php" );

require(__DIR__ . "/../vendor/autoload.php");

require( __DIR__ . "/page_component.php" );
require( __DIR__ . "/api_data_fetcher.php" );
