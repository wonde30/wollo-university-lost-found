<?php

namespace App\Support\Enums;

enum ClaimEvidenceType: string
{
    case PHOTO = 'photo';
    case RECEIPT = 'receipt';
    case SERIAL_NUMBER = 'serial_number';
    case STUDENT_ID = 'student_id';
    case OTHER_DOCUMENT = 'other_document';
}
