<?php

namespace App\Support;

use App\Services\Security\SensitiveDataMasker;

class Masked
{
    /** @param mixed $value */
    public static function value(mixed $value): mixed
    {
        return app(SensitiveDataMasker::class)->mask($value);
    }
}
