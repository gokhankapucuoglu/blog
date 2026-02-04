<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SpatieActivityLogTrait;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SpatieActivityLogTrait;
}
