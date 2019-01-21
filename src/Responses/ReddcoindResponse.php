<?php

declare(strict_types=1);

namespace IndieBlock\Reddcoin\Responses;

use IndieBlock\Reddcoin\Traits\Collection;
use IndieBlock\Reddcoin\Traits\ReadOnlyArray;
use IndieBlock\Reddcoin\Traits\SerializableContainer;

class ReddcoindResponse extends Response implements
    \ArrayAccess,
    \Countable,
    \Serializable,
    \JsonSerializable
{
    use Collection, ReadOnlyArray, SerializableContainer;
}
