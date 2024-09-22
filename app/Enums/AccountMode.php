<?php

namespace App\Enums;

enum AccountMode: string
{
    case ONLINE = 'online';
    case ONLINE_OFFLINE = 'online_offline';
}
