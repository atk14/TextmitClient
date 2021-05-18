<?php
class TcTextmit extends TcBase {

	function _test(){
		$textmit = new \Textmit\Client(array(
			"api_data_fetcher" => new TestingApiDataFetcher(TEXTMIT_API_BASE_URL)
		));

		// \Textmit\Client::addDocument()

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

		// using FulltextData
		$pc = new PageComponent(444);
		$fd = new \Textmit\FulltextData($pc,"sk");
		$fd->addText("Lorem Ipsum");
		$fd->addText("dolor sit amet");;
		$ret = $textmit->addDocument($fd->toArray());
		$params = $ret["params"];
		$this->assertEquals("Lorem Ipsum dolor sit amet",$params["c"]);
		$this->assertEquals("page_component",$params["type"]);
		$this->assertEquals("sk",$params["language"]);
		$this->assertEquals(444,$params["id"]);

		// \Textmit\Client::removeDocument()

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

	function test_FulltextData(){
		$pc = new PageComponent(444);
		$fd = new \Textmit\FulltextData($pc,"cs");

		$fd->setMetaData("key1 key2");
		$fd->addMetaData("key3");

		$ary = $fd->toArray();
		$this->assertEquals("key1 key2 key3",$ary["meta_data"]);

		$fd->setMetaData("key4 key5");

		$ary = $fd->toArray();
		$this->assertEquals("key4 key5",$ary["meta_data"]);
	}

	function test_EmptySearchResult(){
		$textmit = new \Textmit\Client(array(
			"api_data_fetcher" => new TestingApiDataFetcher(TEXTMIT_API_BASE_URL)
		));

		$result = $textmit->search("");
		$this->assertEquals(0,$result->getTotalAmount());
		$this->assertEquals(0,$result->getOffset());
		$this->assertEquals(100,$result->getLimit());
		$this->assertEquals("Textmit\EmptySearchResult",get_class($result));

		$result = $textmit->search(" ",array(
			"offset" => 20,
			"limit" => 10,
		));
		$this->assertEquals(0,$result->getTotalAmount());
		$this->assertEquals(20,$result->getOffset());
		$this->assertEquals(10,$result->getLimit());
		$this->assertEquals("Textmit\EmptySearchResult",get_class($result));

		$result = $textmit->search("Sample text");
		$this->assertEquals("Textmit\SearchResult",get_class($result));
	}
}
