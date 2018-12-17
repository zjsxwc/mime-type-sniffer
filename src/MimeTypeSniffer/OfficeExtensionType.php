<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 15/12/2018
 * Time: 3:54 PM
 */

namespace MimeTypeSniffer;


class OfficeExtensionType
{

    /**
     * @var int
     */
    private $docType;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var int
     */
    private $extensionLength;

    /**
     * OfficeExtensionType constructor.
     * @param int $docType
     * @param string $extension
     */
    public function __construct($docType, $extension)
    {
        $this->docType = $docType;
        $this->extension = $extension;
        $this->extensionLength = strlen($extension);
    }

    /**
     * @return int
     */
    public function getDocType()
    {
        return $this->docType;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return int
     */
    public function getExtensionLength()
    {
        return $this->extensionLength;
    }
}