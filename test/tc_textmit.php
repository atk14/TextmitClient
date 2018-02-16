<?php
class TcTextmit extends TcBase {

	function test(){
		$textmit = new Textmit();

		// Textmit::addDocument()

		$ret = $textmit->addDocument(123,"Sample text");
		$params = $ret["params"];
		$this->assertEquals("article",$params["type"]); // TEXTMIT_DEFAULT_DOCUMENT_TYPE
		$this->assertEquals("en",$params["language"]); // TEXTMIT_DEFAULT_LANGUAGE
		$this->assertEquals(123,$params["id"]);
		$this->assertEquals("Sample text",$params["c"]);

		// the same document, another way
		$ret2 = $textmit->addDocument(array(
			"id" => 123,
			"c" => "Sample text",
		));
		$this->assertEquals($ret,$ret2);

		$ret3 = $textmit->addDocument(array(
			"id" => 124,
			"c" => "Sample text",
		));
		$this->assertNotEquals($ret,$ret3);
		$this->assertEquals(124,$ret3["params"]["id"]);

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

		// id must be set

		try {
			$textmit->addDocument(array("id" => "", "c" => "Sample text"));
			$this->fail();
		}catch(Exception $e){
			$this->assertContains("id is missing",$e->getMessage());
		}

		// Textmit::removeDocument()

		$ret = $textmit->removeDocument(124);
		$this->assertEquals("documents/destroy",$ret["action"]);
		$params = $ret["params"];
		$this->assertEquals("article",$params["type"]);
		$this->assertEquals(124,$params["id"]);

		$ret = $textmit->removeDocument(125,"attachment");
		$params = $ret["params"];
		$this->assertEquals("attachment",$params["type"]);
		$this->assertEquals(125,$params["id"]);

		$pc = new PageComponent(111);
		$ret = $textmit->removeDocument($pc);
		$params = $ret["params"];
		$this->assertEquals("page_component",$params["type"]);
		$this->assertEquals(111,$params["id"]);
	}
}
