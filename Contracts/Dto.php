<?php

declare (strict_types=1);

namespace Sam\DataObjects\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

interface Dto extends JsonSerializable, Arrayable
{
}
