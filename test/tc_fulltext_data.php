<?php
class TcFulltextData extends TcBase {

	function test(){

		$fd = new FulltextData("article","cs");

		$fd->addText("Article Title","b");
		$fd->addText("Example perex","b");
		$fd->addText("Body, body, body"); // default weight is "c"
		$fd->setMetaData("for_members for_testers");

		$ary = $fd->toArray();
		$this->assertEquals(array (
			"type" => "article",
			"language" => "cs",
			"date" => "",
			"a" => "",
			"b" => " Article Title Example perex",
			"c" => " Body, body, body",
			"d" => "",
			"meta_data" => "for_members for_testers",
		),$ary);
	}
}
