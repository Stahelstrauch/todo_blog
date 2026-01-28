<?php

namespace App\Models\Concerns;

use Carbon\CarbonInterface;

trait FormatsDateTimes {
    // Üks keskne formaat kogu projektile
    protected const DATETIME_FORMAT = 'd.m.Y H:i:s';

    protected function formatDateTime(?CarbonInterface $value): ?string {
        return $value?->format(static::DATETIME_FORMAT);
    }
}
