<?php

namespace App\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case FILE = 'file';
    case VOICE_NOTE = 'voice-note';
    case CONTACT = 'contact';
}
