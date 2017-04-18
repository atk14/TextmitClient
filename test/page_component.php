<?php
// a mock model class
class PageComponent {
	var $id;

	function __construct($id = 123){
		$this->id = $id;
	}

	function getId(){ return $this->id; }
}
