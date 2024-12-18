<?php

namespace App\Enums;

enum UserStatus: string
{
    const Active = 'active';
    const Inactive = 'inactive';
    const Suspended = 'suspended';

}
