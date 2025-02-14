<?php

namespace App\Enums;

enum ROLE: string
{
    case ADMIN = 'admin';
    case ORGANIZER = 'organizer';
    case ATTENDEE = 'attendee';
}
