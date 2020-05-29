<?php namespace MapGuesser\Database;

class Utils {
    public static function backtick(string $name) {
        return '`' . $name . '`';
    }
}
