<?php
class TcFulltextData extends TcBase {

	function test(){
		$fd = new \Textmit\FulltextData("article","cs");

		$fd->addText("Article Title","b");
		$fd->addText("Example perex","b");
		$fd->addText("Body, body, body"); // default weight is "c"
		$fd->setMetaData("for_members for_testers");

		$ary = $fd->toArray();
		$this->assertEquals(array (
			"id" => null,
			"type" => "article",
			"language" => "cs",
			"date" => "",
			"a" => "",
			"b" => "Article Title Example perex",
			"c" => "Body, body, body",
			"d" => "",
			"meta_data" => "for_members for_testers",
		),$ary);

		// addHtml

		$fd = new \Textmit\FulltextData("article","cs");
		$fd->addHtml("
			<style>
				.h3 { font-size: 3em; }
			</style>
			<!-- article body -->
			<h3>Hi there!</h3>
			<p>It's been a while since we've heard about <em>Ben &amp; Bob</em>...</p>
		","c");
		$ary = $fd->toArray();
		$this->assertEquals("Hi there! It's been a while since we've heard about Ben & Bob...",$ary["c"]);

		//

		$pc = new PageComponent();
		$fd = new \Textmit\FulltextData($pc);
		$fd->addText("Sample page content");

		$ary = $fd->toArray();
		$this->assertEquals(array (
			"id" => 123,
			"type" => "page_component",
			"language" => "en", // TEXTMIT_DEFAULT_LANGUAGE
			"date" => "",
			"a" => "",
			"b" => "",
			"c" => "Sample page content",
			"d" => "",
			"meta_data" => "",
		),$ary);
	}
}
