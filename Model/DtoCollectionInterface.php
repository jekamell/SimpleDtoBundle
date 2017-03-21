<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Model;

/**
 * Interface DtoCollectionInterface
 */
interface DtoCollectionInterface extends DtoInterface, \Iterator, \Countable, \ArrayAccess
{
}
