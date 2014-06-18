<?php
/**
 * Created by taopeng@meilishuo.com.
 * User: 2014
 * Date: 14-6-18
 * Time: 上午10:49
 */

class ABtestLog {

    private static $log = array();

    public static function write($segment) {
        self::$log[$segment] = 1;
    }

    public static function dump() {
        return "abtest=" . implode("_", sort(array_keys(self::$log)));
    }

} 