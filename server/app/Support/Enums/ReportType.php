<?php

namespace App\Support\Enums;

enum ReportType: string
{
    case ITEM_LIST = 'item_list';
    case RESOLUTION_TIME = 'resolution_time';
    case USER_ACTIVITY = 'user_activity';
    case CLAIM_SUMMARY = 'claim_summary';
    case AUDIT_EXPORT = 'audit_export';
    case SEARCH_ANALYTICS = 'search_analytics';
}
