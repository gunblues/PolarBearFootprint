<?php

class MyActionModel {

    //write your own action here
    static function execute($data) {
		MyRedis::lpush("footprint", json_encode($data));
    }
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
