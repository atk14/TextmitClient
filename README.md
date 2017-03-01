Textmit Client
==============

This is a client for indexing and searching engine [Texmit.com](http://www.textmit.com/). The client is written in PHP. It can be very easily integrated into an ATK14 application. 

Basic usage
-----------

In the configuration file set the TEXTMIT_API_KEY constant.

    define("TEXTMIT_API_KEY","123.eeee.abcde....");

Where do you get the TEXTMIT_API_KEY? Well at the moment the Textmit Engine is closed beta. So you need an invitation code in order to get the key. We are sorry.

Indexing:

    $textmit = new Textmit();
    $textmit->addDocument(123,array(
      //"type" => "document",
      //"language" => "en",
      "a" => "The most relevant textual part",
      "d" => "Less relevant textual part",
      "c" => "Textual part with the default relevance",
      "d" => "The least relevant textual part"
    ));

Searching:

    $result = $textmit->search("vitamins and minerals",array(
      "type" => "article",
      "language" => "en",
      "offset" => 0,
      "limit" => 20,
    ));
    echo $result->getTotalAmount();
    print_r($result->getIds()); // array("123","124"...)

Installation
------------

Use the Composer to install the Texmit Client.

    cd path/to/your/project/
    composer require atk14/textmit-client dev-master

Licence
-------

Files is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
