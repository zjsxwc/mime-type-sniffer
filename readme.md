
一个把Chrome浏览器的C++ MimeType检测代码， 移植到php里，

Chrome的源C++代码地址是： https://github.com/chromium/chromium/blob/9deef45cc5f77319506088554cd40aa521e9df8d/net/base/mime_sniffer.cc


使用方式
```php

$mimeTypeResult = "";


(new MimeTypeSniffer())
    ->sniffMimeType($fileFullPath, $mimeTypeResult, $fileOriginalName);


echo $mimeTypeResult;

```
