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

    $textmit->addDocument(123,[
      "type" => "article",
      "language" => "en",
      "a" => "The most relevant textual part",
      "d" => "More relevant textual part",
      "c" => "Textual part with the default relevance",
      "d" => "The least relevant textual part"
    ]);

The same object can be indexed in different languages.

    $textmit->addDocument(123,[
      "type" => "article",
      "language" => "cs",
      "a" => "Nejvíce relevantní část textu",
      "d" => "Více relevantní část textu",
      "c" => "Textová část s výchozí relevancí",
      "d" => "Nejméně relevantní část textu"
    ]);

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

More types of documents can be search at once.

    $result = $textmit->search("vitamins and minerals",[
      "language" => "cs",
      "types" => ["article","page","image_gallery","video"],
    ]);

    foreach($result->getItems() as $item){
      $id = $item->getId();
      $type = $item->getType(); // "article", "page", "image_gallery" or "video'

      $object = $item->getObject(); // Article#123, Page#332, ImageGallery#453...
    }

Installation
------------

Use the Composer to install the Texmit Client.

    cd path/to/your/project/
    composer require atk14/textmit-client

Licence
-------

Files is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
