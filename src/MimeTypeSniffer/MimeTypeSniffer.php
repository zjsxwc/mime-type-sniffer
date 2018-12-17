<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 15/12/2018
 * Time: 3:54 PM
 */

namespace MimeTypeSniffer;



class MimeTypeSniffer
{

    private $enoughContentByteSize = 512;

    /**
     * @var MagicNumber[]
     */
    private $magicNumbers;

    /**
     * @var MagicNumber[]
     */
    private $extraMagicNumbers;


    /**
     * @var MagicNumber[]
     */
    private $magicXMLOrHTML;

    /**
     * @var MagicNumber[]
     */
    private $officeMagicNumbers;

    /**
     * @var OfficeExtensionType[]
     */
    private $officeExtensionTypes;


    private function initialize()
    {
        $this->magicNumbers = [
            new MagicNumber("application/pdf", "%PDF-"),
            new MagicNumber("application/postscript", "%!PS-Adobe-"),
            new MagicNumber("image/gif", "GIF87a"),
            new MagicNumber("image/gif", "GIF89a"),
            new MagicNumber("image/png", "\x89" . "PNG\x0D\x0A\x1A\x0A"),
            new MagicNumber("image/jpeg", "\xFF\xD8\xFF"),
            new MagicNumber("image/bmp", "BM"),
            // Source: Mozilla
            new MagicNumber("text/plain", "#!"),  // Script
            new MagicNumber("text/plain", "%!"),  // Script, similar to PS
            new MagicNumber("text/plain", "From"),
            new MagicNumber("text/plain", ">From"),
            // Chrome specific
            new MagicNumber("application/x-gzip", "\x1F\x8B\x08"),
            new MagicNumber("audio/x-pn-realaudio", "\x2E\x52\x4D\x46"),
            new MagicNumber("video/x-ms-asf",
                "\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C"),
            new MagicNumber("image/tiff", "I I"),
            new MagicNumber("image/tiff", "II*"),
            new MagicNumber("image/tiff", "MM\x00*"),
            new MagicNumber("audio/mpeg", "ID3"),
            new MagicNumber("image/webp", "RIFF....WEBPVP"),
            new MagicNumber("video/webm", "\x1A\x45\xDF\xA3"),
            new MagicNumber("application/zip", "PK\x03\x04"),
            new MagicNumber("application/x-rar-compressed", "Rar!\x1A\x07\x00"),
            new MagicNumber("application/x-msmetafile", "\xD7\xCD\xC6\x9A"),
            new MagicNumber("application/octet-stream", "MZ"),

            new MagicNumber("application/x-chrome-extension", "Cr24\x02\x00\x00\x00"),
            new MagicNumber("application/x-chrome-extension", "Cr24\x03\x00\x00\x00"),

            // Sniffing for Flash:
            //
            //   new MagicNumber("application/x-shockwave-flash", "CWS"),
            //   new MagicNumber("application/x-shockwave-flash", "FLV"),
            //   new MagicNumber("application/x-shockwave-flash", "FWS"),
            //
            // Including these magic number for Flash is a trade off.
            //
            // Pros:
            //   * Flash is an important and popular file format
            //
            // Cons:
            //   * These patterns are fairly weak
            //   * If we mistakenly decide something is Flash, we will execute it
            //     in the origin of an unsuspecting site.  This could be a security
            //     vulnerability if the site allows users to upload content.
            //
            // On balance, we do not include these patterns.
        ];


        $this->officeMagicNumbers = [
            new MagicNumber("CFB", "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"),
            new MagicNumber("OOXML", "PK\x03\x04"),
        ];


        $this->magicXMLOrHTML = [
            new MagicNumber("application/atom+xml", "<feed", true),
            new MagicNumber("application/rss+xml", "<rss", true),
            new MagicNumber("application/xml", "<?xml", true),
        ];
        foreach (["!DOCTYPE html", "script", "html", "!--", "head", "iframe", "h1", "div", "font", "table", "a", "style", "title", "b", "body", "br", "p"] as $htmlTag) {
            $this->magicXMLOrHTML[] = new MagicNumber("text/html", "<" . $htmlTag, true);
        }


        $this->extraMagicNumbers = [
            new MagicNumber("image/x-xbitmap", "#define"),
            new MagicNumber("image/x-icon", "\x00\x00\x01\x00"),
            new MagicNumber("image/svg+xml", "<?xml_version="),
            new MagicNumber("audio/wav", "RIFF....WAVEfmt "),
            new MagicNumber("video/avi", "RIFF....AVI LIST"),
            new MagicNumber("audio/ogg", "OggS\0"),
            new MagicNumber("video/mpeg", "\x00\x00\x01\xB0", false, "\xFF\xFF\xFF\xF0"),
            new MagicNumber("audio/mpeg", "\xFF\xE0", false, "\xFF\xE0"),
            new MagicNumber("video/3gpp", "....ftyp3g"),
            new MagicNumber("video/3gpp", "....ftypavcl"),
            new MagicNumber("video/mp4", "....ftyp"),
            new MagicNumber("video/quicktime", "....moov"),
            new MagicNumber("application/x-shockwave-flash", "CWS"),
            new MagicNumber("application/x-shockwave-flash", "FWS"),
            new MagicNumber("video/x-flv", "FLV"),
            new MagicNumber("audio/x-flac", "fLaC"),
            // Per https://tools.ietf.org/html/rfc3267#section-8.1
            new MagicNumber("audio/amr", "#!AMR\n"),

            // RAW image types.
            new MagicNumber("image/x-canon-cr2", "II\x2a\x00\x10\x00\x00\x00CR"),
            new MagicNumber("image/x-canon-crw", "II\x1a\x00\x00\x00HEAPCCDR"),
            new MagicNumber("image/x-minolta-mrw", "\x00MRM"),
            new MagicNumber("image/x-olympus-orf", "MMOR"),  // big-endian
            new MagicNumber("image/x-olympus-orf", "IIRO"),  // little-endian
            new MagicNumber("image/x-olympus-orf", "IIRS"),  // little-endian
            new MagicNumber("image/x-fuji-raf", "FUJIFILMCCD-RAW "),
            new MagicNumber("image/x-panasonic-raw",
                "IIU\x00\x08\x00\x00\x00"),  // Panasonic .raw
            new MagicNumber("image/x-panasonic-raw",
                "IIU\x00\x18\x00\x00\x00"),  // Panasonic .rw2
            new MagicNumber("image/x-phaseone-raw", "MMMMRaw"),
            new MagicNumber("image/x-x3f", "FOVb"),
        ];


        $this->officeExtensionTypes = [
            new OfficeExtensionType(OfficeDocType::WORD, ".doc"),
            new OfficeExtensionType(OfficeDocType::EXCEL, ".xls"),
            new OfficeExtensionType(OfficeDocType::POWERPOINT, ".ppt"),
            new OfficeExtensionType(OfficeDocType::WORD, ".docx"),
            new OfficeExtensionType(OfficeDocType::EXCEL, ".xlsx"),
            new OfficeExtensionType(OfficeDocType::POWERPOINT, ".pptx"),
        ];

    }

    /**
     * MimeTypeSniffer constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }


    /**
     * @param string $magic
     * @param string $content
     * @param int $len
     * @return bool
     */
    private function magicCmp($magic, $content, $len)
    {
        $cursor = 0;
        while ($len) {
            $magicChar = ord(substr($magic, $cursor, 1));
            $contentChar = ord(substr($content, $cursor, 1));
            if (($magicChar !== ord(".")) && ($magicChar !== $contentChar)) {
                return false;
            }
            $cursor++;
            $len--;
        }
        return true;
    }


    /**
     * @param string $magic
     * @param string $content
     * @param int $len
     * @param string $mask
     * @return bool
     */
    private function magicMaskCmp($magic, $content, $len, $mask)
    {
        $cursor = 0;
        while ($len) {
            $magicChar = ord(substr($magic, $cursor, 1));
            $contentChar = ord(substr($content, $cursor, 1));
            $maskChar = ord(substr($mask, $cursor, 1));
            if (($magicChar !== ord(".")) && ($magicChar !== ($maskChar & $contentChar))) {
                return false;
            }
            $cursor++;
            $len--;
        }
        return true;
    }

    /**
     * @param string $content
     * @param int $size
     * @param MagicNumber $magicNumber
     * @param string $result
     * @return bool
     */
    private function matchMagicNumber($content, $size, $magicNumber, &$result)
    {
        $len = $magicNumber->getMagicLength();
        $match = false;

        if ($magicNumber->isString()) {
            $match = strcmp(strtolower(substr($content, 0, $len)), $magicNumber->getMagic()) === 0;
        } else {
            if ($size > $len) {
                if ($magicNumber->getMask()) {
                    $match = $this->magicMaskCmp($magicNumber->getMagic(), $content, $len, $magicNumber->getMask());
                } else {
                    $match = $this->magicCmp($magicNumber->getMagic(), $content, $len);
                }
            }
        }

        if ($match) {
            $result = $magicNumber->getMimeType();
            return true;
        }

        return false;
    }

    /**
     * @param string $content
     * @param int $size
     * @param MagicNumber[] $magicNumbers
     * @param string $result
     * @return bool
     */
    private function checkForMagicNumbers($content, $size, $magicNumbers, &$result)
    {
        foreach ($magicNumbers as $magicNumber) {
            if ($this->matchMagicNumber($content, $size, $magicNumber, $result)) {
                return true;
            }
        }
        return false;
    }


    private function getEnoughContent($path)
    {
        $filename = $path;
        $handle = fopen($filename, "r");
        $content = fread($handle, $this->enoughContentByteSize);
        fclose($handle);
        return $content;
    }


    /**
     * @param $path
     * @param $result
     * @param $filename
     * @return bool
     */
    public function sniffMimeType($path, &$result, $filename)
    {
        $content = $this->getEnoughContent($path);
        $result = "application/unknown";

        if ($this->sniffForOfficeDocs($content, $filename, $result)) {
            return true;
        }
        if ($this->sniffForMagicNumbers($content, $result)) {
            return true;
        }
        if ($this->sniffForExtraMagicNumbers($content, $result)) {
            return true;
        }
        if ($this->sniffForXMLOrHTML($content, $result)) {
            return true;
        }

        return $this->sniffBinary($content, $result);
    }


    private function sniffForMagicNumbers($content, &$result)
    {
        return $this->checkForMagicNumbers($content, strlen($content), $this->magicNumbers, $result);
    }

    private function sniffForExtraMagicNumbers($content, &$result)
    {
        return $this->checkForMagicNumbers($content, strlen($content), $this->extraMagicNumbers, $result);
    }

    private function looksLikeBinary($content)
    {
        // The definition of "binary bytes" is from the spec at
        // https://mimesniff.spec.whatwg.org/#binary-data-byte
        //
        // The bytes which are considered to be "binary" are all < 0x20. Encode them
        // one bit per byte, with 1 for a "binary" bit, and 0 for a "text" bit. The
        // least-significant bit represents byte 0x00, the most-significant bit
        // represents byte 0x1F.
        $kBinaryBits = ~(1 << ord("\t") | 1 << ord("\n") | 1 << ord("\r") | 1 << ord("\f") | 1 << ord("\x1b"));
        for ($i = 0; $i < strlen($content); $i++) {
            $byte = ord(substr($content, $i, 1));
            if ($byte < 0x20 && ($kBinaryBits & (1 << $byte))) {
                return true;
            }
        }
        return false;
    }

    private function sniffBinary($content, &$result)
    {
        $byteOrderMark = [
            new MagicNumber("text/plain", "\xFE\xFF"),  // UTF-16BE
            new MagicNumber("text/plain", "\xFF\xFE"),  // UTF-16LE
            new MagicNumber("text/plain", "\xEF\xBB\xBF"),  // UTF-8
        ];
        if ($this->checkForMagicNumbers($content, strlen($content), $byteOrderMark, $result)) {
            return false;
        }
        if ($this->looksLikeBinary($content)) {
            $result = "application/octet-stream";
            return true;
        }
        $result = "text/plain";
        return false;
    }


    private function sniffForXMLOrHTML($content, &$result)
    {
        return $this->checkForMagicNumbers($content, strlen($content), $this->magicXMLOrHTML, $result);
    }

    private function sniffForOfficeDocs($content, $filename, &$result)
    {
        $officeVersion = "";
        if (!$this->checkForMagicNumbers($content, strlen($content), $this->officeMagicNumbers, $officeVersion)) {

            $_ = "";
            if ($this->checkForMagicNumbers($content, strlen($content), [new MagicNumber("application/xml", "<?xml", true)], $_)) {
                if (strpos($content, "mso-application") !== false) {
                    $officeVersion = "XML";
                    goto PROCESS_TYPE_GUESS;
                }
            }

            return false;
        }


        PROCESS_TYPE_GUESS:

        $type = OfficeDocType::NONE;
        foreach ($this->officeExtensionTypes as $officeExtensionType) {
            $extension = substr($filename, strlen($filename) - $officeExtensionType->getExtensionLength());
            if (strcmp(strtolower($extension), $officeExtensionType->getExtension()) === 0) {
                $type = $officeExtensionType->getDocType();
                break;
            }
        }

        if ($type === OfficeDocType::NONE) {
            return false;
        }
        if ((strcmp($officeVersion, "CFB") === 0)||(strcmp($officeVersion, "XML") === 0)) {
            switch ($type) {
                case OfficeDocType::WORD:
                    $result = "application/msword";
                    return true;
                case OfficeDocType::EXCEL:
                    $result = "application/vnd.ms-excel";
                    return true;
                case OfficeDocType::POWERPOINT:
                    $result = "application/vnd.ms-powerpoint";
                    return true;
                default:
                    return false;
            }
        } else if (strcmp($officeVersion, "OOXML") === 0) {
            switch ($type) {
                case OfficeDocType::WORD:
                    $result = "application/vnd.openxmlformats-officedocument." .
                        "wordprocessingml.document";
                    return true;
                case OfficeDocType::EXCEL:
                    $result = "application/vnd.openxmlformats-officedocument." .
                        "spreadsheetml.sheet";
                    return true;
                case OfficeDocType::POWERPOINT:
                    $result = "application/vnd.openxmlformats-officedocument." .
                        "presentationml.presentation";
                    return true;
                default:
                    return false;
            }
        }
        return false;
    }


}