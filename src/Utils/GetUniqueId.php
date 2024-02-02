<?php

namespace App\Utils;

use Ramsey\Uuid\Uuid;

class GetUniqueId
{
    public static function generateUniqueSessionId(): string
    {
        return Uuid::uuid4()->toString();
    }
}
