<?php
namespace Textmit;

interface Indexable {
	
	/**
	 *
	 * @return boolean
	 */
	public function isIndexable();

	/**
	 *
	 * 	$fulltext_data = $object->getFulltextData("en"); // "cs", "sk", "hu", "en"...
	 *
	 * @param string $lang
	 * @return Textmit\FulltextData
	 */
	public function getFulltextData($lang);

}
