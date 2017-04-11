<?php
if(!class_exists("Textmit")){ // nacteni textmit.php -> ocekavame, ze jsou definovany konstanty TEXTMI_*
	throw new Exception("Class Textmit doesn't exist");
}

/**
 *	class Article extends ApplicationModel {
 *
 *		// ...
 *
 *		function getFulltextData() {
 *			$fd = new FulltextData("article","cs");
 *			$fd->addText($article->getTitle(),"b");
 *			$fd->addText($article->getPerex(),"b");
 *			$fd->addHtml($article->geBody()); // default weight is "c"
 *			foreach($article->getAttachments() as $attachment){
 *				$fd->merge($attachment->getFulltextData(),array("a" => "d", "b" => "d", "c" => "d"));
 *			}
 *			return $fd;
 *		}
 *
 *	}
 *
 *  $textmit = new Textmit();
 *	$textmit->addDocument($article->getId(),$fd->toArray());
 */
class FulltextData {

	protected $language = null;
	protected $type = null;
	protected $date = "";
	protected $a = "";
	protected $b = "";
	protected $c = "";
	protected $d = "";
	protected $meta_data = "";

	function __construct($type = TEXTMIT_DEFAULT_DOCUMENT_TYPE, $language = TEXTMIT_DEFAULT_LANGUAGE){
		$this->type = $type;
		$this->language = $language;
	}

	/**
	 * $fd->addText($text); // default weight is "c"
	 * $fd->addText($text,"a");
	 * $fd->addText($text,array("weight" => "a"));
	 */
	function addText($text,$options = array()){
		if(is_string($options)){
			$options = array("weight" => $options);
		}
		$options += array(
			"weight" => "c",
		);

		$weight = $options["weight"];
		$weight = strtolower($weight);

		if(!in_array($weight,array("a","b","c","d"))){
			throw new Exception("Unknown text weight: $weight");
		}

		$text = trim($text);
		if(!strlen($text)){
			return;
		}

		$this->$weight .= " ".$text;
	}

	function addHtml($text,$options = array()){
		$text = strip_tags($text);
		$text = html_entity_decode($text);
		$this->addText($text,$options);
	}

	/**
	 * $fd->setMetaData("keyword_1 keyword_2 keyword_3");
	 */
	function setMetaData($meta_data){
		$this->meta_data = (string)$meta_data;
	}

	/**
	 *	$fd = $article->getFulltextData();
	 *	foreach($article->getAttachments() as $a){
	 *		$fd->merge($a->getFulltextData(),array("a" => "c", "b" => "c"));
	 *	}
	 */
	function merge($fd,$weight_tr = array()){
		// from -> to
		$weight_tr += array(
			"a" => "a",
			"b" => "b",
			"c" => "c",
			"d" => "d",
		);
		$data = $fd->toArray();
		foreach(array("a","b","c","d") as $w){
			$this->addText($data[$w],$weight_tr[$w]);
		}
	}

	function setDate($date){
		$this->date = $date;
	}

	function toArray(){
		return array(
			"type" => $this->type,
			"language" => $this->language,
			"date" => $this->date,
			"a" => $this->a,
			"b" => $this->b,
			"c" => $this->c,
			"d" => $this->d,
			"meta_data" => $this->meta_data,
		);
	}
}
