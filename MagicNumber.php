<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 15/12/2018
 * Time: 3:52 PM
 */

namespace MimeTypeSniffer;

class MagicNumber
{
    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string
     */
    private $magic;


    /**
     * @var int
     */
    private $magicLength;

    /**
     * @var bool
     */
    private $isString;

    /**
     * @var string
     */
    private $mask;

    /**
     * MagicNumber constructor.
     * @param string $mimeType
     * @param string $magic
     * @param bool $isString
     * @param string $mask
     */
    public function __construct($mimeType, $magic, $isString = false, $mask = "")
    {
        $this->mimeType = $mimeType;
        $this->magic = $magic;
        $this->magicLength = strlen($magic);
        $this->isString = $isString;
        if ($mask && (strlen($mask) !== strlen($magic))) {
            throw new \RuntimeException("magic and mask sizes must be equal");
        }
        $this->mask = $mask;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getMagic()
    {
        return $this->magic;
    }

    /**
     * @return bool
     */
    public function isString()
    {
        return $this->isString;
    }

    /**
     * @return string
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @return int
     */
    public function getMagicLength()
    {
        return $this->magicLength;
    }


}