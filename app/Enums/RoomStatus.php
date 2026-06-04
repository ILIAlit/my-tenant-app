<?php

namespace App\Enums;

enum RoomStatus: string
{
    case FREE = "free";
    case USED = "used";
    case REPAIR = "repair";
}
