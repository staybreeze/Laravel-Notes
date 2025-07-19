<?php

namespace App\Services;

interface DowntimeNotifier
{
    public function notify(string $message): void;
} 