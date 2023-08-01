Textmit Client
==============

This is a client for indexing and searching engine [Texmit.com](https://www.textmit.com/api/). The client is written in PHP. It can be very easily integrated into an ATK14 application. 

## 1. Basic usage

In the configuration file set the TEXTMIT_API_KEY constant.

    define("TEXTMIT_API_KEY","123.eeee.abcde....");

Where do you get the TEXTMIT_API_KEY? Well at the moment the Textmit Engine is closed beta. So you need an invitation code in order to get the key. We are sorry.

### 1.1 Adding a document to the index

    $textmit = new \Textmit\Client();

    // The socket timeout can be optionally increased
    $adf = $this->textmit->getApiDataFetcher();
    $adf->setSocketTimeout(30.0); // seconds

    $textmit->addDocument([
      "type" => "article",
      "id" => 123,
      "language" => "en",
      "a" => "The most relevant textual part",
      "d" => "More relevant textual part",
      "c" => "Textual part with the default relevance",
      "d" => "The least relevant textual part"
    ]);

The same object can be indexed in a different language.

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

### 1.2 Preparing fulltext data

For easing process of fulltext data preparation, class FulltextData can be used.

    $article = Article::GetInstanceById(333);

    $fd_article = new \Textmit\FulltextData($article);
    $fd_article->addHtml($article->getBody());
    $fd_article->addText($addText->getTitle(),"a");
    $fd_article->setDate($article->getPublishedAt()); // "2018-02-17 06:00:00"

    $textmit->addDocument($fd_article->toArray());

FulltextData has method merge() for merging other FulltextData object, e.g. one can merge FulltextData of an Image into FulltextData of an Article. During merging, text weights of the merging object can be changed (typically lowered).

    $fd_article->merge($fd_image,[
      "a" => "c",
      "b" => "c"
    ]);

### 1.3 Searching

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

You can use "prefix search" to search without matching whole words.

    // search for documents containing words vita, vitae, vitamine, vitality...
    $result = $textmit->search("vita",[
      "prefix_search" => true,
    ]);
    

### 1.4 Deleting documents from the index

#### 1.4.1 Deleting a single document

    $textmit->removeDocument(123,"article");

#### 1.4.2 Deleting the whole index

Method ```destroyStage``` deletes all documents of all types in the current stage. One project can have more stages, e.g. "PRODUCTION", "DEVELOPMENT", "TEST"...

    $textmit->destroyStage();

#### 1.4.3 Deleting outdated documents

One technique of indexing a small project (small website) is to index everything once in every day and then remove outdated documents. Outdated documents are those that have not been indexed or re-indexed after the given threshold date.

    $textmit->removeObsoleteDocuments(date("Y-m-d H:i:s",time() - 60 * 60 * 24)); // 1 day

### 1.5 Configuration

There are several configuration constants.

    define("TEXTMIT_API_KEY","...");
    define("TEXTMIT_DEFAULT_LANGUAGE","en"); // "en", "cs"
    define("TEXTMIT_DEFAULT_DOCUMENT_TYPE","article");
    define("TEXTMIT_STAGE","auto"); // "DEVELOPMENT", "PRODUCTION", "auto" means auto detection - it leads to "PRODUCTION", "DEVELOPMENT@hostname" or "TEST@hostname"
    define("TEXTMIT_API_BASE_URL","https://www.textmit.com/api/"); // This is default base url

### 1.6 Tracy panel integration

The Textmit package comes with Panel for easy integration into a popular debugger Tracy (https://packagist.org/packages/tracy/tracy)

    $tracy_bar = Tracy\Debugger::getBar();
    $tracy_bar->addPanel(new Textmit\Panel());

## 2. Installation

Use the Composer to install the Texmit Client.

    cd path/to/your/project/
    composer require atk14/textmit-client

## 3. Extended integration into an ATK14 project

### 3.1 Using Indexable interface in searchable models

    <?php
    // file: app/models/article.php
    class Article extends ApplicationModel implements Translatable, \Textmit\Indexable {

      static function GetTranslatableFields() { return array("title", "teaser", "body"); }

      function isPublished(){
        return strtotime($this->getPublishedAt())<time();
      }
      
      function isIndexable(){
        return $this->isPublished();
      }

      function getFulltextData($lang){
        $fd = new \Textmit\FulltextData($this);
        $fd->addText($this->getTitle($lang),"a");
        $fd->addHtml($this->getTeaser($lang),"b");
        $fd->addHtml($this->getBody($lang)); // default is section "c" 
        $fd->setDate($this->getPublishedAt());

        return $fd;
      }
    }

### 3.2 Robot for automatic document indexing

    <?php
    // file: robots/fulltext_indexer_robot.php
    class FulltextIndexerRobot extends ApplicationRobot {

      function run(){
        $this->textmit = new \Textmit\Client();
        $adf = $this->textmit->getApiDataFetcher();
        $adf->setSocketTimeout(30.0);

        $this->now = $now = date("Y-m-d H:i:s");

        $this->logger->info("using stage: ".$this->textmit->getStage());
        $this->logger->flush();

        $RECIPE_ITEMS = [
          "Article" => ["conditions" => ["published_at<=:now"], "bind_ar" => [":now" => $now]],
        ];

        foreach($RECIPE_ITEMS as $class => $options){
          foreach($class::FindAll($options) as $object){
            $this->_indexObject($object);
          }
        }

        $deleted = $this->textmit->removeObsoleteDocuments(date("Y-m-d H:i:s",time() - 60 * 60 * 24 * 2)); // 2 days
        $this->logger->info("obsolete documents deleted: $deleted");
      }

      function _indexObject($object){
        global $ATK14_GLOBAL;

        $obj_str = get_class($object)."#".$object->getId(); // e.g. "Article#123"

        $this->logger->info("about to index $obj_str");
        $this->logger->flush();

        if(method_exists($object,"isIndexable") && !$object->isIndexable()){
          $this->textmit->removeDocument($object);
          $this->logger->debug("object $obj_str is not indexable: (removed if exists)");
          return;
        }

        foreach($ATK14_GLOBAL->getSupportedLangs() as $lang){
          $fd = $object->getFulltextData($lang);
          $stat = $this->textmit->addDocument($fd->toArray());
          if(!$stat){
            $this->logger->warn("adding $obj_str failed");
          }else{
            $this->logger->debug("successfully indexed: $obj_str");
          }
        }
      }
    }


## 4. Licence

Textmit Client is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
