<?php
namespace App\Contracts;

interface EventPusher
{
    public function push($event);
} 