<?php

namespace App\Enums;

enum AccountMode: string
{
    case Online = 'online';
    case OnlineOffline = 'online_offline';
}
