<?php

class MyActionModel {

    //write your own action here
    static function execute($data) {

        if (empty($data["url"])) {
            return;
        }

        MyRedis::init();
        MyRedis::lpush("footprint", json_encode($data));

        if (array_key_exists("away", $data) === false || $data["away"] !== 1) {
            $host = parse_url($data["url"], PHP_URL_HOST);
            MyRedis::lpush("page", json_encode(
                array(
                    "id" => $host . "_" . sha1($data["url"]),
                    "url" => $data["url"]
                )
            ));
        }
    }
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
