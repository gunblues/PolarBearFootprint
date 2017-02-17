<?php

class ApiBaseController extends Yaf_Controller_Abstract {
	
	public $jsonp = false;

    public function init () {
        Yaf_Dispatcher::getInstance()->disableView();
        MyRedis::init();
    }

	public function validateParameter($ret, $schema) {

		foreach($schema['required'] as $required) {
			if (!array_key_exists($required, $ret)) {
				$this->ouputJsonErr(ErrorCodeModel::JSON_REQUIRED_KEY, $required);
				return false;
			}
		}

		foreach($schema['dataType'] as $key => $val) {
			if (array_key_exists($key, $ret)) {
				if (filter_var($ret[$key], $val) === false) {
					$this->ouputJsonErr(ErrorCodeModel::JSON_DATA_TYPE_INVALID, $key);
					return false;
}
			}
		}

		return true;
	}

    public function getJson() {
		if (!$this->jsonp) {
			if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
				$this->ouputJsonErr(ErrorCodeModel::REQUEST_METHOD_MUST_BE_POST);
				return false;
			}
			
			$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
			if(stripos($contentType, 'application/json') !== 0){
				$this->ouputJsonErr(ErrorCodeModel::CONTENT_TYPE_MUST_BE_APPLICATION_JSON);
				return false;
			}
			
			$content = trim(file_get_contents("php://input"));
		} else {
			$content = $this->getRequest()->getQuery('json');
		}
		
		$decoded = json_decode($content, true);
		
		if(!is_array($decoded)){
			$this->ouputJsonErr(ErrorCodeModel::RECEIVED_CONTENT_CONTAINED_INVALID_JSON);
			return false;
		}

		return $decoded;
    }

	public function ouputJsonErr($errcode, $extra = "", $jsonp = false) {
		$msg = ErrorCodeModel::$message[$errcode];
        $res = array('errcode' => $errcode, 'errmsg' => $msg.$extra);
error_log($this->getRequest()->getQuery('callback'));
		if ($this->jsonp) {
        	$this->getResponse()->setBody($this->getRequest()->getQuery('callback') . '(' . json_encode($res) . ')');
				
		} else {
        	$this->getResponse()->setHeader('Content-type', 'application/json;charset=utf8');
        	$this->getResponse()->setBody(json_encode($res));
		}
	}

    public function outputJson($errcode = 0, $errmsg = 'success', $data = null, $jsonp = false) {
        $res = array('errcode' => $errcode, 'errmsg' => $errmsg);
		if ($data !== null) {
			$res['data'] = $data;
		}

		if ($this->jsonp) {
        	echo $this->getRequest()->getQuery('callback') . '(' . json_encode($res) . ')';
				
		} else {
        	$this->getResponse()->setHeader('Content-type', 'application/json;charset=utf8');
        	$this->getResponse()->setBody(json_encode($res));
		}
    }
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
