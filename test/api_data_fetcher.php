<?php
// This is a *testing* ApiDataFetcher
class ApiDataFetcher {

	var $api_base_url = "";
	var $init_options = array();

	function __construct($api_base_url,$options = array()){
		$this->api_base_url = $api_base_url;
		$this->init_options = $options;
	}

	function get($action,$params){
		return $this->_result("GET",$action,$params);
	}

	function post($action,$params){
		return $this->_result("POST",$action,$params);
	}

	function _result($method,$action,$params){
		return array(
			"method" => $method, // "GET", "POST"
			"action" => $action,
			"params" => $params,
			"api_base_url" => $this->api_base_url,
			"init_options" => $this->init_options,
		);
	}
}
