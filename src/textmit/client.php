<?php

namespace Textmit;

/**
 * A client for Textmit - the Indexing Service
 *
 * See https://www.textmit.com/ for more info
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
 *	$textmit = new \Textmit\Client();
 *	$textmit->addDocument([
 *		"type" => "article",
 *		"id" => 123,
 *		"language" => "en",
 *		"a" => "The most relevant textual part",
 *		"d" => "Less relevant textual part",
 *		"c" => "Textual part with the default relevance",
 *		"d" => "The least relevant textual part"
 *	]);
 *
 *	$result = $textmit->search("vitamins and minerals",[
 *		"type" => "article",
 *		"language" => "en",
 *		"offset" => 0,
 *		"limit" => 20,
 *	]);
 *	echo $result->getTotalAmount();
 *	print_r($result->getIds()); // ["123","124"...]
 *
 */

defined("TEXTMIT_API_KEY") 								|| define("TEXTMIT_API_KEY","some_secret_key");
defined("TEXTMIT_DEFAULT_LANGUAGE") 			|| define("TEXTMIT_DEFAULT_LANGUAGE","cs"); // "cs", "en"
defined("TEXTMIT_DEFAULT_DOCUMENT_TYPE") 	|| define("TEXTMIT_DEFAULT_DOCUMENT_TYPE","article");
defined("TEXTMIT_STAGE") 									|| define("TEXTMIT_STAGE","auto"); // "auto", "PRODUCTION", "DEVELOPMENT@asterix"
defined("TEXTMIT_API_BASE_URL") 					|| define("TEXTMIT_API_BASE_URL","https://www.textmit.com/api/");

class Client {

	const VERSION = "1.4.3";

	protected $api_data_fetcher = null;

	protected $api_key;
	protected $default_language;
	protected $default_document_type;
	protected $stage;

	function __construct($options = array()){
		$options += array(
			"api_base_url" => TEXTMIT_API_BASE_URL,

			"api_key" => TEXTMIT_API_KEY,
			"default_language" => TEXTMIT_DEFAULT_LANGUAGE,
			"default_document_type" => TEXTMIT_DEFAULT_DOCUMENT_TYPE,
			"stage" => TEXTMIT_STAGE,

			"api_data_fetcher" => null
		);

		$this->api_data_fetcher = $options["api_data_fetcher"];
		if(is_null($this->api_data_fetcher)){
			$this->api_data_fetcher = new \ApiDataFetcher($options["api_base_url"],array(
				"lang" => "en", // the language of api messages, not the language of indexed documents
				"user_agent" => sprintf("TextmitClient/%s ApiDataFetcher/%s UrlFetcher/%s",self::VERSION,\ApiDataFetcher::VERSION,\UrlFetcher::VERSION),
			));
		}

		$this->api_key = $options["api_key"];
		$this->default_language = $options["default_language"];
		$this->default_document_type = $options["default_document_type"];
		$this->stage = $options["stage"];
	}

	/**
	 * Adds document to the fulltext index
	 *
	 *	$article = Article::GetInstanceById(123);
	 *
	 *	$textmit->addDocument(array(
	 *		"type" => "article",
	 *		"id" => $article->getId(),
	 *		"c" => "Text of the document"
	 *	));
	 *
	 *	// or
	 *
	 *	$textmit->addDocument($article,"Text of the document");
	 *
	 *	// or
	 *
	 *	$textmit->addDocument($article->getId(),array(
	 *		"type" => "article",
	 *		"a" => "The most relevant relevant textual part",
	 *		"c" => "The rest of the documents text"
	 *	));
	 *
	 *	// or
	 *
	 *	$textmit->addDocument("123","Text of the document"); // This will work when the TEXTMIT_DEFAULT_DOCUMENT_TYPE is defined as article
	 *
	 *  // or the preferred method with FulltextData
	 *
	 *	$fulltext_data = $article->getFulltextData(); // See FulltextData for more information
	 *	$textmit->addDocument($fulltext_data->toArray());
	 */
	function addDocument($id_or_options,$options = array()){
		if(is_a($id_or_options,"\Textmit\FulltextData")){
			$id_or_options = $id_or_options->toArray();
		}

		if(is_array($id_or_options)){
			$options = $id_or_options;
			$id = isset($options["id"]) ? $options["id"] : null;
		}else{
			$id = $id_or_options;
		}

		list($_default_type,$id) = $this->_determineDocumentTypeAndId($id);

		if(strlen($id)==0){
			throw new \Exception("Textmit::addDocument(): id is missing");
		}

		if(is_string($options)){
			$options = array("c" => $options);
		}

		$options += array(
			"type" => $_default_type,
			"language" => $this->default_language,
			"date" => "", // e.g. "2015-08-13 11:01:00"
			"rank_multiplier" => 1.0,
			"a" => "",
			"b" => "",
			"c" => "",
			"d" => "",
			"meta_data" => "",
		);

		$options["id"] = $id;
		$options["stage"] = $this->_getStage();
		$options["auth_token"] = $this->_getAuthToken();

		$apf = $this->getApiDataFetcher();
		$data = $apf->post("documents/create_new",$options);
		return $data;
	}

	/**
	 * Removes the given document from the fulltext index
	 * 
	 *	$article = Article::GetInstanceById(123);
	 *
	 *	$textmit->removeDocument($article);
	 *
	 *	// or
	 *
	 *	$textmit->removeDocument(123,"article");
	 */
	function removeDocument($id,$type = null){
		list($_default_type,$id) = $this->_determineDocumentTypeAndId($id);

		if(is_null($type)){ $type = $_default_type; }
		$params = array(
			"id" => $id,
			"type" => $type,
			"stage" => $this->_getStage(),
			"auth_token" => $this->_getAuthToken(),
		);
		$apf = $this->getApiDataFetcher();
		$data = $apf->post("documents/destroy",$params);
		return $data;
	}

	/**
	 * Removes documents added or updated before limit_date
	 *
	 *	$textmit->removeObsoleteDocuments("2017-04-27 00:00:00");
	 */
	function removeObsoleteDocuments($limit_date = null){
		if(!$limit_date){ $limit_date = date("Y-m-d H:i:s",time() - 60 * 60 * 24 * 30); } // a month

		$params = array(
			"stage" => $this->_getStage(),
			"limit_date" => $limit_date,
			"auth_token" => $this->_getAuthToken(),
		);
		$apf = $this->getApiDataFetcher();
		$data = $apf->post("obsolete_documents/bulk_destroy",$params);
		return $data["documents_deleted"];
	}

	/**
	 * Performs searching in the fulltext index
	 *
	 *	$result = $textmit->search("vitamins and minerals"); // TextmitResult
	 *	$result = $textmit->search("vitamins and minerals",array("type" => "article"));
	 */
	function search($query,$params = array()){
		$query = trim($query);
		$query = preg_replace('/\s+/s',' ',$query);

		$params += array(
			"stage" => $this->_getStage(),
			"type" => null,
			"types" => array(), // array("article","attachment")
			"language" => $this->default_language,
			"prefix_search" => false, // false - matches whole words; true - matches according to the beginning of words
			"offset" => 0,
			"limit" => 100,
			"auth_token" => $this->_getAuthToken(),
		);

		if($query===""){
			return new EmptySearchResult($params);
		}

		if(mb_strlen($query)>200){
			return new EmptySearchResult($params);
		}

		if($params["type"]){
			$params["types"][] = $params["type"];
		}
		unset($params["type"]);

		$params["types"] = join("\n",$params["types"]);
		$params["query"] = $query;

		$apf = $this->getApiDataFetcher();
		$data = $apf->get("documents/search",$params);
		return new SearchResult($data);
	}

	function getStage(){ return $this->_getStage(); }

	/**
	 * Lists all stages
	 *
	 *	$stages = $textmit->listStages();
	 *	print_r($stages);
	 */
	function listStages(){
		$apf = $this->getApiDataFetcher();
		return $apf->get("stages/index",array(
			"auth_token" => $this->_getAuthToken(),
		));
	}

	/**
	 * Deletes all documents in the given stage
	 *
	 *	$textmit->destroyStage(); // it destroys the current stage
	 *	$textmit->destroyStage("DEVELOPMENT@prcek");
	 */
	function destroyStage($name = null){
		if(!isset($name)){ $name = $this->_getStage(); }
		$apf = $this->getApiDataFetcher();
		return $apf->post("stages/destroy",array(
			"stage" => $name,
			"auth_token" => $this->_getAuthToken(),
		));
	}

	function getApiDataFetcher(){
		return $this->api_data_fetcher;
	}

	/**
	 * Returns current valid auth token
	 *
	 *	echo $textmit->getAuthToken(); // e.g. "10.f2cd5ec9038eda62de5565e6134459e1a57a0d8841264d81b7050ef4841c215b"
	 */
	function getAuthToken(){
		return $this->_getAuthToken();
	}

	protected function _getAuthToken(){
		$time = time();
		$t = $time - ($time % (60 * 10)); // new auth_token every 10 minutes
		$ar = explode(".",$this->api_key);
		$id = (int)$ar[0];
		return $id.".".hash("sha256",$this->api_key.$t);
	}

	protected function _getStage(){
		if($this->stage=="auto"){
			if(PRODUCTION){ return "PRODUCTION"; }
			$stage = DEVELOPMENT ? "DEVELOPMENT" : "TEST";
			$hostname = gethostname();
			return "$stage@$hostname"; // DEVELOPMENT@asterix
		}
		return $this->stage;
	}

	protected function _determineDocumentTypeAndId($id){
		$type = $this->default_document_type;

		if(is_object($id)){
			$type = \String4::ToObject(get_class($id))->underscore()->toString(); // "PageComponent" -> "page_component"
			$id = $id->getId();
		}

		return array($type,$id);
	}
}
