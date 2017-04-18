<?php
class TcTextmit extends TcBase {

	function test(){
		$textmit = new Textmit();

		$ret = $textmit->addDocument(123,"Sample text");
		$params = $ret["params"];
		$this->assertEquals("article",$params["type"]); // TEXTMIT_DEFAULT_DOCUMENT_TYPE
		$this->assertEquals("en",$params["language"]); // TEXTMIT_DEFAULT_LANGUAGE
		$this->assertEquals(123,$params["id"]);
		$this->assertEquals("Sample text",$params["c"]);

		$ret = $textmit->addDocument(333,array(
			"type" => "attachment",
			"language" => "cs",
			"b" => "Title",
			"c" => "Attachment content",
		));
		$params = $ret["params"];
		$this->assertEquals("attachment",$params["type"]);
		$this->assertEquals("cs",$params["language"]);
		$this->assertEquals(333,$params["id"]);
		$this->assertEquals("Title",$params["b"]);
		$this->assertEquals("Attachment content",$params["c"]);


		$pc = new PageComponent(222);
		$ret = $textmit->addDocument($pc,"Page content");
		$params = $ret["params"];
		$this->assertEquals("page_component",$params["type"]);
		$this->assertEquals("en",$params["language"]);
		$this->assertEquals(222,$params["id"]);
		$this->assertEquals("Page content",$params["c"]);
	}
}
