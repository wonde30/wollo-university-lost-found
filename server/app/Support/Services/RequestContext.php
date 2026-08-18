<?php

namespace App\Support\Services;

use App\Models\User;
use Illuminate\Http\Request;

class RequestContext
{
    public function __construct(protected Request $request)
    {}

    public function user(): ?User
    {
        return $this->request->user();
    }

    public function ip(): ?string
    {
        return $this->request->ip();
    }

    public function userAgent(): ?string
    {
        return $this->request->userAgent();
    }
}
