<?php
/**
 * Created by taopeng@meilishuo.com.
 * User: 2014
 * Date: 14-6-18
 * Time: 上午10:49
 */

class ABtestLog {

    /*
     *  生成的日志形如： abtest:A1_B2, 最为mobsnake日志ext_logs中的一个kv。
     */

    private static $log = array();

    public static function write($segment) {
        self::$log[$segment] = 1;
    }

    public static function dump() {
        if ( empty(self::$log)) {
	    return "end";
        }else {
            return "abtest:" . implode("_", sort(array_keys(self::$log))). "end";
        }
    }

    public static function clear() {
        self::$log = array();
    }

} 