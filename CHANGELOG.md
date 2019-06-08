Change Log
==========

All notable changes to this project will be documented in this file.

[1.2] - 2019-06-08
------------------

- Added interface Textmit\Indexable
- Textmit\FulltextData object can be passed to the Textmit\Client::addDocument()
- Added public method Textmit\Client::getAuthToken()

[1.1] - 2018-03-23
------------------

### Added
- Added Textmit\Panel for integration into the popular debugger Tracy

[1.0] - 2018-02-17
------------------

Classes renamed and placed into namespace \Textmit\ (breaks backwards compatibility)

Old code needs to be corrected this way:

    // $textmit = new Textmit();
    $textmit = new \Textmit\Client()

    // $fd = new FulltextData();
    $fd = new \Textmit\FulltextData();

[0.2] - 2018-02-17
------------------

- Textmit::addDocument() tuned

[0.1] - 2018-02-16
------------------

- First tagged version of the TextmitClient
