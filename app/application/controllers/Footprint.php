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
		$data = $this->getJson();
		if ($data === false) {
			return;
		}

		if (!$this->validateParameter($data, array(
			'required' => array('fp', 'url', 'ts'),
			'dataType' => array(
				'url' => FILTER_VALIDATE_URL,
				'ts' => FILTER_VALIDATE_INT,
				'away'=> FILTER_VALIDATE_INT
			)
		))) {
			return;
		}

		$data['ip'] = MyUtil::getIpAddress();

		MyActionModel::execute($data);
        $this->outputJson();
	}
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
