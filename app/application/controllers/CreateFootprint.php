<?php
/**
 * @name CreateFootprintController
 * @author gunblues
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */

class CreateFootprintController extends ApiBaseController 
{
    public function indexAction() {
		$ret = $this->getJson();
		if ($ret === false) {
			return;
		}

		if (!$this->validateParameter($ret, array(
			'required' => array('fp', 'url', 'ts'),
			'dataType' => array(
				'url' => FILTER_VALIDATE_URL,
				'ts' => FILTER_VALIDATE_INT
			)
		))) {
			return;
		}

		$ret['ip'] = MyUtil::getIpAddress();

		MyRedis::lpush("footprint", json_encode($ret));
        $this->outputJson();
	}
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
