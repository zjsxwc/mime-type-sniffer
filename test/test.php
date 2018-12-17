<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 17/12/2018
 * Time: 8:41 AM
 */

include "autoload.php";


function testDoc()
{
    $mimeTypeResult = "";
    (new \MimeTypeSniffer\MimeTypeSniffer())
        ->sniffMimeType(__DIR__ . "/files/test.doc", $mimeTypeResult, "test.doc");
    assert("application/msword" === $mimeTypeResult);
}
testDoc();


function testDocx()
{
    $mimeTypeResult = "";
    (new \MimeTypeSniffer\MimeTypeSniffer())
        ->sniffMimeType(__DIR__ . "/files/test.docx", $mimeTypeResult, "test.doc");
    assert("application/vnd.openxmlformats-officedocument.wordprocessingml.document" === $mimeTypeResult);
}
testDocx();

