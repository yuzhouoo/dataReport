<?php
/**
 * 自定义Controller基类
 */
class MY_Controller extends CI_Controller {

	public $arrUser = [];
	public $PlatForm = 'client';

	public function __construct() {
		parent::__construct();
		if($_SERVER['HTTP_HOST'] == 'adata.adduode.com'){
			$this->PlatForm = 'admin';
		}
		$this->load->model('UserModel');
		$this->arrUser = $this->UserModel->checkLogin($this->PlatForm);
	}


	/**
	 *json 输出
	 *
	 * @param $array
	 * @bool $bolJsonpSwitch
	 */
	protected function outJson($arrData, $intErrCode, $strErrMsg=null,$bolJsonpSwitch = false) {
		header("Content-Type:application/json");
		$arrData = ErrCode::format($arrData, $intErrCode, $strErrMsg);
		echo json_encode($arrData);
	}

}
?>
