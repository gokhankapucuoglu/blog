<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SpatieActivityLogTrait;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use SpatieActivityLogTrait;
}
