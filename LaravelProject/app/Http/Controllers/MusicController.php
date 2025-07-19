<?php
namespace App\Http\Controllers;

use App\Services\AppleMusic;
use Illuminate\Http\Request;

class MusicController
{
    protected $apple;

    public function __construct(AppleMusic $apple)
    {
        $this->apple = $apple;
    }

    public function play()
    {
        return $this->apple->play();
    }
} 