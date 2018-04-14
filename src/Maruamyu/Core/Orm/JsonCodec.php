<?php

namespace Maruamyu\Core\Orm;

/**
 * json_encode and json_decode
 */
class JsonCodec
{
    /**
     * @param mixed $value
     * @return string
     */
    public static function encode($value)
    {
        $encodeOptions = JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES;
        return json_encode($value, $encodeOptions);
    }

    /**
     * @param string $json
     * @return array
     */
    public static function decode($json)
    {
        return json_decode($json, true);
    }

    /**
     * constructor is private
     */
    private function __construct()
    {
    }
}
