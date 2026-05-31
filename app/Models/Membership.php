<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Membership extends Pivot
{
    #[\Override]
    protected $table = 'team_user';

    #[\Override]
    public $incrementing = true;
}
