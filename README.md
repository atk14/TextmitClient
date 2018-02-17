Textmit Client
==============

This is a client for indexing and searching engine [Texmit.com](http://www.textmit.com/). The client is written in PHP. It can be very easily integrated into an ATK14 application. 

Basic usage
-----------

In the configuration file set the TEXTMIT_API_KEY constant.

    define("TEXTMIT_API_KEY","123.eeee.abcde....");

Where do you get the TEXTMIT_API_KEY? Well at the moment the Textmit Engine is closed beta. So you need an invitation code in order to get the key. We are sorry.

### Indexing

    $textmit = new Textmit();

    $textmit->addDocument([
      "type" => "article",
      "id" => 123,
      "language" => "en",
      "a" => "The most relevant textual part",
      "d" => "More relevant textual part",
      "c" => "Textual part with the default relevance",
      "d" => "The least relevant textual part"
    ]);

The same object can be indexed in different languages.

    $textmit->addDocument([
      "type" => "article",
      "id" => 123,
      "language" => "cs",
      "a" => "Nejvíce relevantní část textu",
      "d" => "Více relevantní část textu",
      "c" => "Textová část s výchozí relevancí",
      "d" => "Nejméně relevantní část textu"
    ]);

Here is the shortest way how to add a document to the fulltext index. Text is weighted as "c". Default language is used.

    $text->addDocument($article,"Lorem Ipsum");

### Searching

Searching can be performed in one specific language.

    $result = $textmit->search("vitamins and minerals",[
      "type" => "article",
      "language" => "en",
      "offset" => 0,
      "limit" => 20,
    ]);
    $records_found = $result->getTotalAmount();
    print_r($result->getIds()); // ["123","124"...]

More types of document can be searched at once.

    $result = $textmit->search("vitamins and minerals",[
      "language" => "cs",
      "types" => ["article","page","image_gallery","video"],
    ]);

    foreach($result->getItems() as $item){
      $id = $item->getId();
      $type = $item->getType(); // "article", "page", "image_gallery" or "video'

      $object = $item->getObject(); // Article#123, Page#332, ImageGallery#453...
    }

### Configuration

There are several configuration constants.

    define("TEXTMIT_API_KEY","...");
    define("TEXTMIT_DEFAULT_LANGUAGE","en"); // "en", "cs"
    define("TEXTMIT_DEFAULT_DOCUMENT_TYPE","article");
    define("TEXTMIT_STAGE","auto"); // "auto" means auto detection
    define("TEXTMIT_API_BASE_URL","http://www.textmit.com/api/"); // This is default base url

Installation
------------

Use the Composer to install the Texmit Client.

    cd path/to/your/project/
    composer require atk14/textmit-client

Licence
-------

Files is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
