<?php
namespace App\Services;

class Transistor
{
    protected $parser;

    public function __construct(PodcastParser $parser)
    {
        $this->parser = $parser;
    }

    public function upload($feed)
    {
        return $this->parser->parse($feed) . ' → 上傳成功';
    }
} 