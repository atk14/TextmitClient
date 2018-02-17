Change Log
==========

All notable changes to this project will be documented in this file.

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
