<?php

namespace App\Helper;

class Format
{
    /**
     * Minify HTML and XML
     *
     * @param string $data
     * @return string $data Returns minified data
     */
    public static function minify($data)
    {
        $search = array(
            '/\>[^\S ]+/s', // strip white spaces after tags, except space
            '/[^\S ]+\</s', // strip white spaces before tags, except space
            '/(\s)+/s' // shorten multiple white space sequences
        );

        $replace = array('>','<','\\1');
        $data = preg_replace($search, $replace, $data);

        return $data;
    }
}
