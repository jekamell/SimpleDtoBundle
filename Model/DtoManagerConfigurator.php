<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;

class DtoManagerConfigurator
{
    /** @var string */
    protected $collectionKey;
    /** @var string */
    protected $formatDate;
    /** @var string */
    protected $formatDateTime;

    /**
     * DtoConfigurator constructor.
     * @param string $collectionKey
     * @param string $formatDate
     * @param string $formatDateTime
     */
    public function __construct($collectionKey, $formatDate, $formatDateTime)
    {
        $this->collectionKey = $collectionKey;
        $this->formatDate = $formatDate;
        $this->formatDateTime = $formatDateTime;
    }

    /**
     * @param DtoManager $dtoManager
     */
    public function configure(DtoManager $dtoManager): void
    {
        $dtoManager->setConfigurator($this);
    }

    /**
     * @return string
     */
    public function getCollectionKey()
    {
        return $this->collectionKey;
    }

    /**
     * @param string $collectionKey
     */
    public function setCollectionKey($collectionKey)
    {
        $this->collectionKey = $collectionKey;
    }

    /**
     * @return string
     */
    public function getFormatDate()
    {
        return $this->formatDate;
    }

    /**
     * @param string $formatDate
     */
    public function setFormatDate($formatDate)
    {
        $this->formatDate = $formatDate;
    }

    /**
     * @return string
     */
    public function getFormatDateTime()
    {
        return $this->formatDateTime;
    }

    /**
     * @param string $formatDateTime
     */
    public function setFormatDateTime($formatDateTime)
    {
        $this->formatDateTime = $formatDateTime;
    }
}
