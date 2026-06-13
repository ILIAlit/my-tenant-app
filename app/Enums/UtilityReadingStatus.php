<?php

namespace App\Enums;

enum UtilityReadingStatus: string
{
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
