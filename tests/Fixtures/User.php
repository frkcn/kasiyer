<?php

namespace Frkcn\Kasiyer\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Notifications\Notifiable;
use Frkcn\Kasiyer\Billable;

class User extends Model
{
    use Billable, Notifiable;

    protected $guarded = [];
}
