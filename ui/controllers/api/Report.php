<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *@投放的所有广告
 */
class Report extends MY_Controller{
	public $PlatForm = 'client';
	public function __construct(){
		parent::__construct();
		$this->load->library('DbUtil');
		$this->load->model('ReportModel');
		
		if($_SERVER['HTTP_HOST'] == 'adata.adduode.com'){
			$this->PlatForm = 'admin';
		}
	}

	/* 获取用户列表 */
	public function getUserList(){
		$res = $this->ReportModel->queryUserList();
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'查询成功');
	}

	/* 获取游戏报搞数据 */
	public function get(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}
		$arrParams = $this->input->get(NULL,TRUE);	
		$arrParams['endDate'] = (!isset($arrParams['endDate']) || !strtotime($arrParams['endDate'])) ? strtotime(date("Ymd")) : strtotime($arrParams['endDate']);
		$arrParams['startDate'] = (!isset($arrParams['startDate']) || !strtotime($arrParams['startDate'])) ? $arrParams['endDate'] - 31 * 86400 : strtotime($arrParams['startDate']);
		$arrParams['current'] = isset($arrParams['current']) && !empty($arrParams['current']) ? $arrParams['current'] : '1';
		$arrParams['pageSize'] =  isset($arrParams['pageSize']) && !empty($arrParams['pageSize']) ?  $arrParams['pageSize'] : '5';
		
		$arrParams['platform'] = $this->PlatForm;
		$res = $this->ReportModel->getGameData($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'查询失败');
		}

		return $this->outJson($res,ErrCode::OK,'查询成功');
	}

	/* 添加游戏数据记录 */
	public function add(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}
		
		if(isset($_SESSION['account_id'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'无权限访问');
		}

		$arrParams = json_decode(file_get_contents('php://input'), true);
		//$arrParams = $this->input->post(NULL,TRUE);
		foreach($arrParams as $key => $val){
			if(empty($val)){
				return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
			}
		}

		$res = $this->ReportModel->addGameData($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'添加失败');
		}

		return  $this->outJson($res,ErrCode::OK,'添加成功');
	}

	/* 修改游戏数据记录 */
	public function modify(){
		$arrParams = json_decode(file_get_contents('php://input'), true);
		//$arrParams = $this->input->post(NULL,TRUE);
		
		if(!isset($arrParams['gid']) ||	
			empty($arrParams['gid']) || 
			!isset($arrParams['game_name']) || 
			empty($arrParams['game_name']) || 
			!is_numeric($arrParams['activate_num']) || 
			!is_numeric($arrParams['price']) || 
			!isset($arrParams['acc_id']) || 
			empty($arrParams['acc_id'])){
				return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数错误');
			}

		$res = $this->ReportModel->udpGameData($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'修改失败');
		}

		return  $this->outJson($res,ErrCode::OK,'修改成功');

	}

	/* 删除游戏数据记录 */
	public function del(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}
		if(!isset($_SESSION['bg_account_id'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'无权限访问');
		}

		$arrParams = json_decode(file_get_contents('php://input'), true);
		//$arrParams = $this->input->post(NULL,TRUE);
		if(!isset($arrParams['gid']) || empty($arrParams['gid']) || !isset($arrParams['accId']) || empty($arrParams['accId'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数失败');
		}

		$res = $this->ReportModel->delGameData($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'删除失败');
		}

		return $this->outJson($res,ErrCode::OK,'删除成功');	
	}

	/* 获取单条游戏数据 */
	public function getItemGameData(){
		if(empty($this->arrUser)){
			return $this->outJson('', ErrCode::ERR_NOT_LOGIN);
		}
		$arrParams = $this->input->get(NULL,TRUE);
	
		if(!isset($arrParams['gid']) || empty($arrParams['gid']) || !isset($arrParams['accId']) || empty($arrParams['accId'])){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'参数失败');
		}

		$res = $this->ReportModel->queryItemGameData($arrParams);
		if(!$res){
			return $this->outJson('',ErrCode::ERR_INVALID_PARAMS,'查询失败');
		}
		return $this->outJson($res,ErrCode::OK,'查询成功');	
	}
}
