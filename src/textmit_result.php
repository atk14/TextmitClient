<?php
class TextmitResult{
	protected $data;
	function __construct($data){
		$this->data = $data;
	}

	function getTotalAmount(){ return $this->data["total_amount"]; } 
	function getRecordsCount(){ return $this->getTotalAmount(); }
	function getOffset(){ return $this->data["offset"]; }
	function getLimit(){ return $this->data["limit"]; }
	function getRecordsDisplayed(){ return sizeof($this->data["records"]); }
	function atBeginning(){ return $this->getOffset()<=0; }

	function atEnd(){
		if($this->getRecordsCount()==0){ return true; }
		return ($this->getOffset() + $this->getRecordsDisplayed())>=$this->getTotalAmount();
	}

	function getNextOffset(){
		$next_offset = $this->getOffset() + $this->getLimit();
		return $next_offset>($this->getTotalAmount()-1) ? null : $next_offset;
	}

	function getPrevOffset(){
		$prev_offset = $this->getOffset() - $this->getLimit();
		return $prev_offset<=0 ? null : $prev_offset;
	}

	function isEmpty(){ return $this->getTotalAmount()==0; }

	function getItems(){
		$out = array();
		foreach($this->data["records"] as $rec){
			$item = new TextmitResultItem($rec);
			$item->prepareCache();
			$out[] = $item;
		}
		return $out;
	}

	// pro kompatibilitu s TableRecord_Finder
	function getRecords(){
		$out = array();
		foreach($this->getItems() as $item){
			$out[] = $item->getObject();
		}
		return $out;
	}
}

class TextmitResultItem{
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
			return Cache::Get($class_name,$this->getId());
		}
	}

	function prepareCache(){
		$class_name = $this->_getObjectClassName();
		if(class_exists($class_name)){
			return Cache::Prepare($class_name,$this->getId());
		}
	}

	function _getObjectClassName(){
		$class_name = String4::ToObject($this->getType())->camelize()->toString(); // "static_page" -> "StaticPage"
		return $class_name;
	}
}
