<?php

namespace Textmit;

class EmptySearchResult extends SearchResult {

	function __construct($data = array()){
		$data += array(
			"offset" => 0,
			"limit" => 100,
		);
		$data["records"] = array();
		$data["total_amount"] = 0;
		parent::__construct($data);
	}
}
