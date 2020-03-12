<?php

namespace Pagekit\News\Helper;

use Pagekit\Application as App;
use WebPConvert\WebPConvert;

class WebpHelper
{
    public $temp = 'tmp/temp/news';

    public $path;

    public $file;

    public $origin_path;

    public function __construct()
    {
        $this->file = App::file();
        $this->origin_path =  App::get('path');
        $this->path = $this->origin_path.'/'.$this->temp;
        if( !$this->file->exists($this->path) ){
            $this->file->makedir($this->path);
        }
    }

    public function exists( string $name = '')
    { 
        $name = $name.'.webp';
        if( !$this->file->exists( $this->path.'/'.$name ) ){
            return false;
        }
        return $this->temp.'/'.$name;
    }

    public function convert(array $result = []){
        $destinationName = $result['name'].'.webp';
        $destination = $this->path.'/'.$destinationName;
        $converted = WebPConvert::convert(
            $this->origin_path.'/'.$result['origin'],
            $destination,
            [
                'fail' => 'original',
                'serve-image' => [
                    'headers' => [
                        'cache-control' => true,
                        'vary-accept' => true,
                    ],
                    'cache-control-header' => 'max-age=2',
                ],
            
                'convert' => [
                    'quality' => 10
                ],
            ]
        );
        return $this->temp.'/'.$destinationName;
    }
}
