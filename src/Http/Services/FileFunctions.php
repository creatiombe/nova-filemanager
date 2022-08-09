<?php

namespace Grayloon\Filemanager\Http\Services;

trait FileFunctions
{
    /**
     * @param $path
     *
     * @return string
     */
    public function checkPerms($path): string
    {
        clearstatcache(null, $path);

        return decoct(fileperms($path) & 0777);
    }

    /**
     * @param $size
     * @param int $level
     * @param int $precision
     * @param int $base
     *
     * @return string
     */
    public function formatBytes($size, int $level = 0, int $precision = 2, int $base = 1024): string
    {
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $times = floor(log($size, $base));

        return sprintf('%.'.$precision.'f', $size / pow($base, ($times + $level))).' '.$unit[$times + $level];
    }

    /**
     * Clean Slashes.
     *
     * @param $str
     *
     * @return string
     */
    public function cleanSlashes($str): string
    {
        return preg_replace('/([^:])(\/{2,})/', '$1/', $str);
    }

    /**
     * Cleanup filename.
     *
     * @param string $str
     *
     * @return string
     */
    public function fixFilename(string $str): string
    {
        if (! mb_detect_encoding($str, 'UTF-8', true)) {
            $str = utf8_encode($str);
        }
        if (function_exists('transliterator_transliterate')) {
            $str = transliterator_transliterate('Any-Latin; Latin-ASCII', $str);
        } else {
            $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        }
        $str = preg_replace("/[^a-zA-Z0-9\.\[\]_| -]/", '', $str);

        $str = str_replace(['"', "'", '/', '\\'], '', $str);
        $str = strip_tags($str);

        return trim($str);
    }

    /**
     * Cleanup directory name.
     *
     * @param string $str
     *
     * @return  string
     */
    public function fixDirname(string $str): string
    {
        return str_replace(['.', '~', '/', '\\'], '', $str);
    }
}
