<?php

namespace App\Services;

class ServerToolsProvider implements ServerProvider
{
    public function getServerInfo(): string
    {
        return 'ServerTools 伺服器資訊';
    }
} 