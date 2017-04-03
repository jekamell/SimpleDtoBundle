<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;

/**
 * Class DtoManagerConfigurator
 */
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
    public function getCollectionKey(): string
    {
        return $this->collectionKey;
    }

    /**
     * @param string $collectionKey
     */
    public function setCollectionKey(string $collectionKey): void
    {
        $this->collectionKey = $collectionKey;
    }

    /**
     * @return string
     */
    public function getFormatDate(): string
    {
        return $this->formatDate;
    }

    /**
     * @param string $formatDate
     */
    public function setFormatDate(string $formatDate): void
    {
        $this->formatDate = $formatDate;
    }

    /**
     * @return string
     */
    public function getFormatDateTime(): string
    {
        return $this->formatDateTime;
    }

    /**
     * @param string $formatDateTime
     */
    public function setFormatDateTime(string $formatDateTime): void
    {
        $this->formatDateTime = $formatDateTime;
    }
}
