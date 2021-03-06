<?php

class MyActionModel {

    //write your own action here
    static function execute($data) {
        
        MyRedis::init();

        if ($data["fp"] === "cookie") {
            $data["txn_id"] = uniqid(gethostname(), true);
        }

        $nowDate = substr(gmdate('Y-m-d\TH:i:sP'), 0, 19);
        $nowTime = time();

        switch ($data["action"]) {
            case "load":
                if (empty($data["url"])) {
                    return;
                }
        
                $parseUrl = parse_url($data["url"]);

                $port = 80;
                if ($parseUrl["scheme"] === "https") {
                    $port = 443;
                }

                if (!array_key_exists('title', $data)) {
                    $data['title'] = "";
                } else {
                    //for LogStash::Json::ParserError
                    $data['title'] = trim($data['title']);
                    $data['title'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $data['title']);
                    $data['title'] = str_replace(array("\r", "\n"), ' ', $data['title']);
                    $data['title'] = str_replace('"', "'", $data['title']);
                }
                
                $footprint = array(
                         "id" => $data["txn_id"],
                         "title" => $data["title"],
                         "created" => $nowDate,
                         "clientip" => $data["clientip"],
                         "ua" => $data["ua"],
                         "url" => $data["url"], 
                         "scheme" => $parseUrl["scheme"],
                         "hostname" => $parseUrl["host"],
                         "path" => $parseUrl["path"],
                         "port" => $port,
                         "query" => "",
                         "fragment" => "", 
                     );

                if ($data["fp"] !== "cookie") {
                    $footprint["fp"] = $data["fp"];
                    $footprint["cookie"] = "";
                } else if ($data["fp"] === "cookie") {
                    $footprint["cookie"] = $data["sid"];
                    $footprint["fp"] = "";
                }

                $urltask = array(
                         "task_updated" => $nowDate,
                         "url" => $data["url"], 
                         "scheme" => $parseUrl["scheme"],
                         "hostname" => $parseUrl["host"],
                         "path" => $parseUrl["path"],
                         "port" => $port,
                         "query" => "",
                         "fragment" => "", 

                     );

                if (array_key_exists("port", $parseUrl)) {
                    $footprint["port"] = $parseUrl["port"];
                    $urltask["port"] = $parseUrl["port"];
                }

                if (array_key_exists("query", $parseUrl)) {
                    $footprint["query"] = $parseUrl["query"];
                    $urltask["query"] = $parseUrl["query"];

                    $urltaskId = $parseUrl["host"].$parseUrl["path"].$parseUrl["query"];
                } else {
                    $urltaskId = $parseUrl["host"].$parseUrl["path"];
                }
                $urltask["id"] = $parseUrl["host"] . "_" . sha1($urltaskId);
                
                if (array_key_exists("fragment", $parseUrl)) {
                    $footprint["fragment"] = $parseUrl["fragment"];
                    $urltask["fragment"] = $parseUrl["fragment"];
                }

                $jsonFootprint = json_encode($footprint);

                MyRedis::lpush("footprint", $jsonFootprint);
                MyRedis::lpush("urltask", json_encode($urltask));

                if ($data["fp"] !== "cookie") {
                    $fingerprint = array(
                            "id" => $data["fp"],
                            "updated" => $nowDate,
                            "ua" => $data["ua"],
                        );

                    MyRedis::lpush("fingerprint", json_encode($fingerprint));
                } else if ($data["fp"] === "cookie") {
                    $cookie = array(
                            "id" => $data["sid"],
                            "updated" => $nowDate,
                            "ua" => $data["ua"],
                        );

                    MyRedis::lpush("cookie", json_encode($cookie));
                }

                break;
            case "unload":

                $footprint = array(
                        "id" => $data["txn_id"],
                        "stay" => $data["stay"],
                        "clientip" => $data["clientip"],
                    );

                MyRedis::lpush("footprint", json_encode($footprint));

                break;
            case "profile":
                $fingerprint = array(
                        "id" => $data["fp"],
                        "updated" => $nowDate,
                    );

                if (array_key_exists("sex", $data)) {
                    $fingerprint["sex"] = $data["sex"];
                }

                if (array_key_exists("age", $data) && is_int($data["age"])) {
                    $fingerprint["age"] = $data["age"];
                }

                if (array_key_exists("email", $data)) {
                    $fingerprint["email"] = $data["email"];
                }

                MyRedis::lpush("fingerprint", json_encode($fingerprint));

                break;
        }
    }
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
