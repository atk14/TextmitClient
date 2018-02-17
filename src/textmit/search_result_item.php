<?php

namespace Textmit;

class SearchResultItem{

	protected $data;

	function __construct($data){
		$this->data = $data;
	}

	function getId(){
		return $this->data["id"];
	}

	/**
	 * echo $item->getType(); // "article", "static_page"...
	 */
	function getType(){
		return $this->data["type"];
	}

	function getRank(){
		return $this->data["rank"];
	}

	/**
	 * TODO: Nevim, jestli je to takto ok...
	 */
	function getObject(){
		$class_name = $this->_getObjectClassName();
		if(class_exists($class_name)){
			return \Cache::Get($class_name,$this->getId());
		}
	}

	function prepareCache(){
		$class_name = $this->_getObjectClassName();
		if(class_exists($class_name)){
			return \Cache::Prepare($class_name,$this->getId());
		}
	}

	function _getObjectClassName(){
		$class_name = \String4::ToObject($this->getType())->camelize()->toString(); // "static_page" -> "StaticPage"
		return $class_name;
	}
}
