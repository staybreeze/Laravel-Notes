<?php

namespace App\Services\Riak;

class Connection
{
    protected $host;
    protected $port;
    protected $user;
    protected $password;

    public function __construct(array $config)
    {
        $this->host = $config['host'] ?? null;
        $this->port = $config['port'] ?? null;
        $this->user = $config['user'] ?? null;
        $this->password = $config['password'] ?? null;
    }

    public function connect()
    {
        // 這裡可以寫實際連線 Riak 的邏輯
        return "Connected to {$this->host}:{$this->port} as {$this->user}";
    }
} 