<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\architect\services;

/**
 * Class Uid
 */
class Uid
{
    /**
     * Generates a UID
     * @return string
     */
    public static function generate() : string
    {
        return implode(
            '-',
            str_split(
                substr(
                    strtoupper(
                        md5(
                            time() . rand(1000, 9999)
                        )
                    ),
                    0,
                    20
                ),
                4
            )
        );
    }

    /**
     * Generates a UID
     * @return string
     */
    public function generateUid() : string
    {
        return self::generate();
    }
}
