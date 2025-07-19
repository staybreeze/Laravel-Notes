<?php
namespace App\Http\Controllers;

use App\Services\Transistor;
use Illuminate\Http\Request;

class DemoController
{
    protected $transistor;

    public function __construct(Transistor $transistor)
    {
        $this->transistor = $transistor;
    }

    public function upload($feed)
    {
        return $this->transistor->upload($feed);
    }
} 