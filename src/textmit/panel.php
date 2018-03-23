<?php

namespace Textmit;

class Panel extends \ApiDataFetcherPanel {

	function __construct(){
		$client = new Client();
		parent::__construct($client->getApiDataFetcher(),array("title" => "Textmit"));
	}
}
