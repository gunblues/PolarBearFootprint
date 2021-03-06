<?php
/**
 * @name FootprintController
 * @author gunblues
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */

class FootprintController extends ApiBaseController 
{
    public function indexAction() {
        if (MyUtil::isBot()) {
            return;
        }

		$data = $this->getJson();
		if ($data === false) {
			return;
		}

		if (!$this->validateParameter($data, array(
			'required' => array('fp', 'txn_id',  'action'),
			'dataType' => array(
				'url' => FILTER_VALIDATE_URL
			)
		))) {
			return;
		}

		$data['clientip'] = MyUtil::getIpAddress();
		$data['ts'] = time();

		MyActionModel::execute($data);
        $this->outputJson();
	}
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
