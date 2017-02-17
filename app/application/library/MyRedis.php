<?php
class MyRedis
{
    public static $redis = null;

    static function init() {
        if (self::$redis === null) {
            self::$redis = new Redis();
            if(self::$redis->pconnect('127.0.0.1', 6379) === false) {
                error_log("Redis pconnect failed");
                return false;
            }
        }

        return true;
    }

    static function lpush($key, $val) {
		self::$redis->lpush($key, $val);

        return false;
	}
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
