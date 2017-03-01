<?php
/**
 * A client for Textmit - the Indexing Service
 *
 * See http://www.textmit.com/ for more info
 *
 * Usage:
 *
 *	define("TEXTMIT_API_KEY","...");
 *	define("TEXTMIT_DEFAULT_LANGUAGE","en"); // "en", "cs"
 *	define("TEXTMIT_STAGE","auto"); // means auto detection
 *	//define("TEXTMIT_STAGE","PRODUCTION");
 *	//define("TEXTMIT_STAGE","DEVELOPMENT@eniac-22");
 *	//define("TEXTMIT_STAGE","auto");
 *
 *	$textmit = new Textmit();
 *	$textmit->addDocument(123,array(
 *		//"type" => "document",
 *		//"language" => "en",
 *		"a" => "The most relevant textual part",
 *		"d" => "Less relevant textual part",
 *		"c" => "Textual part with the default relevance",
 *		"d" => "The least relevant textual part"
 *	));
 *
 *	$result = $textmit->search("vitamins and minerals",array(
 *		"type" => "article",
 *		"language" => "en",
 *		"offset" => 0,
 *		"limit" => 20,
 *	));
 *	echo $result->getTotalAmount();
 *	print_r($result->getIds()); // array("123","124"...)
 *	
 */

defined("TEXTMIT_API_KEY") 								|| define("TEXTMIT_API_KEY","some_secret_key");
defined("TEXTMIT_DEFAULT_LANGUAGE") 			|| define("TEXTMIT_DEFAULT_LANGUAGE","cs"); // "cs", "en"
defined("TEXTMIT_STAGE") 									|| define("TEXTMIT_STAGE","auto"); // "auto", "PRODUCTION", "DEVELOPMENT@asterix"
defined("TEXTMIT_API_BASE_URL") 					|| define("TEXTMIT_API_BASE_URL","http://www.textmit.com/api/");
defined("TEXTMIT_DEFAULT_DOCUMENT_TYPE") 	|| define("TEXTMIT_DEFAULT_DOCUMENT_TYPE","article");

class Textmit {

	/**
	 *	$textmit->addDocument("123","Text of the document");
	 *	$textmit->addDocument("123",array(
	 *		"type" => "article",
	 *		"a" => "The most relevant relevant textual part",
	 *		"c" => "The rest of the documents text"
	 *	));
	 */
	function addDocument($id,$options = array()){
		if(is_string($options)){
			$options = array("c" => $options);
		}

		$options += array(
			"type" => TEXTMIT_DEFAULT_DOCUMENT_TYPE,
			"language" => TEXTMIT_DEFAULT_LANGUAGE,
			"date" => "", // e.g. "2015-08-13 11:01:00"
			"a" => "",
			"b" => "",
			"c" => "",
			"d" => "",
		);

		$options["id"] = $id;
		$options["stage"] = $this->_getStage();
		$options["auth_token"] = $this->_getAuthToken();

		$apf = $this->_getApiDataFetcher();
		$data = $apf->post("documents/create_new",$options);
		return $data;
	}

	/**
	 * $textmit->removeDocument(123);
	 */
	function removeDocument($id,$type = null){
		if(is_null($type)){ $type = TEXTMIT_DEFAULT_DOCUMENT_TYPE; }
		$params = array(
			"id" => $id,
			"type" => $type,
			"stage" => $this->_getStage(),
			"auth_token" => $this->_getAuthToken(),
		);
		$apf = $this->_getApiDataFetcher();
		$data = $apf->post("documents/destroy",$params);
		return $data;
	}

	/**
	 * $result = $textmit->search("vitamins and minerals"); // TextmitResult
	 * $result = $textmit->search("vitamins and minerals",array("type" => "article"));
	 */
	function search($query,$params = array()){
		$params += array(
			"stage" => $this->_getStage(),
			"type" => null,
			"types" => array(), // array("article","attachment")
			"language" => TEXTMIT_DEFAULT_LANGUAGE,
			"offset" => 0,
			"limit" => 100,
			"auth_token" => $this->_getAuthToken(),
		);

		if($params["type"]){
			$params["types"][] = $params["type"];
		}
		unset($params["type"]);

		$params["types"] = join("\n",$params["types"]);
		$params["query"] = $query;

		$apf = $this->_getApiDataFetcher();
		$data = $apf->get("documents/search",$params);
		return new TextmitResult($data);
	}

	function getStage(){ return $this->_getStage(); }

	/**
	 * $stages = $textmit->listStages();
	 * print_r($stages);
	 */
	function listStages(){
		$apf = $this->_getApiDataFetcher();
		return $apf->get("stages/index",array(
			"auth_token" => $this->_getAuthToken(),
		));
	}

	/**
	 * $textmit->destroyStage(); // it destroys the current stage
	 * $textmit->destroyStage("DEVELOPMENT@prcek");
	 */
	function destroyStage($name = null){
		if(!isset($name)){ $name = $this->_getStage(); }
		$apf = $this->_getApiDataFetcher();
		return $apf->post("stages/destroy",array(
			"stage" => $name,
			"auth_token" => $this->_getAuthToken(),
		));
	}

	function _getAuthToken(){
		$time = time();
		$t = $time - ($time % (60 * 10)); // new auth_token every 10 minutes
		$ar = explode(".",TEXTMIT_API_KEY);
		$id = (int)$ar[0];
		return $id.".".hash("sha256",TEXTMIT_API_KEY.$t);
	}

	function _getApiDataFetcher(){
		$adf = new ApiDataFetcher(TEXTMIT_API_BASE_URL,array(
			"lang" => "cs", // the language of api messages, not the language of indexing document
		));
		return $adf;
	}

	function _getStage(){
		if(TEXTMIT_STAGE=="auto"){
			if(PRODUCTION){ return "PRODUCTION"; }
			$stage = DEVELOPMENT ? "DEVELOPMENT" : "TEST";
			$hostname = gethostname();
			return "$stage@$hostname"; // DEVELOPMENT@asterix
		}
		return TEXTMIT_STAGE;
	}
}
