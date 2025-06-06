<?php

namespace BaksDev\SearchRedis\RediSearch\Fields;

use InvalidArgumentException;

class FieldFactory
{
    public static function make($name, $value, $tagSeparator = ',')
    {
        if (is_array($value)) {
            return (new TagField($name, implode($tagSeparator, $value)))->setSeparator($tagSeparator);
        }
        if ($value instanceof Tag) {
            return new TagField($name, $value);
        }
        if (is_string($value)) {
            return new TextField($name, $value);
        }
        if (is_numeric($value)) {
            return new NumericField($name, $value);
        }
        if ($value instanceof GeoLocation) {
            return new GeoField($name, $value);
        }
        throw new InvalidArgumentException('There is no mapping field type between for the value.');
    }
}
