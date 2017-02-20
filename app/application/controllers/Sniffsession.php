<?php
/**
 * @name SniffsessionController
 * @author gunblues
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */

class SniffsessionController extends Yaf_Controller_Abstract 
{
    public function init () {
        Yaf_Dispatcher::getInstance()->disableView();
        MyRedis::init();
    }

    public function indexAction() {
		$k = "b";
		$v = "";
		if (!isset($_COOKIE[$k])) {
			$v = uniqid(gethostname(), true);
			setcookie($k, $v, time() + (86400 * 365), "/"); 
		} else {
			$v = $_COOKIE[$k];
		}

		$data = array(
			"fp" => "cookie",
			"sid" => $v,
			"ip" => MyUtil::getIpAddress(),
			"ua" => $_SERVER['HTTP_USER_AGENT'],
			"ts" => time(),
		);

		if (array_key_exists("HTTP_REFERER", $_SERVER)) {
			$data["url"] = $_SERVER["HTTP_REFERER"];
		}

		MyActionModel::execute($data);

		header('Content-Type: image/png');
		die("\x89\x50\x4e\x47\x0d\x0a\x1a\x0a\x00\x00\x00\x0d\x49\x48\x44\x52\x00\x00\x00\x01\x00\x00\x00\x01\x01\x03\x00\x00\x00\x25\xdb\x56\xca\x00\x00\x00\x03\x50\x4c\x54\x45\x00\x00\x00\xa7\x7a\x3d\xda\x00\x00\x00\x01\x74\x52\x4e\x53\x00\x40\xe6\xd8\x66\x00\x00\x00\x0a\x49\x44\x41\x54\x08\xd7\x63\x60\x00\x00\x00\x02\x00\x01\xe2\x21\xbc\x33\x00\x00\x00\x00\x49\x45\x4e\x44\xae\x42\x60\x82");	
	}
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
