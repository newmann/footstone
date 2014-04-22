<?php
// +----------------------------------------------------------------------
// | Footstone [ WE CAN DO MORE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Newmannhu <Newmannhu@qq.com> <http://>
// +----------------------------------------------------------------------
namespace Footstone\Model;
use Think\Model;
/**
 * 用户模型
 */
class UserModel extends Model{

	/* 用户模型自动验证 */
	protected $_validate = array(
		/* 验证代码 */
		array('fcode', '1,30', -1, self::EXISTS_VALIDATE, 'length'), //用户代码长度不合法
		array('fcode', 'checkDenyMember', -2, self::EXISTS_VALIDATE, 'callback'), //用户代码禁止注册
		array('fcode', '', -3, self::EXISTS_VALIDATE, 'unique'), //用户代码被占用

		/* 验证密码 */
		array('fpassword', '6,30', -4, self::EXISTS_VALIDATE, 'length'), //密码长度不合法

		/* 验证邮箱 */
		array('femail', 'email', -5, self::EXISTS_VALIDATE), //邮箱格式不正确
		array('femail', '1,32', -6, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
		array('femail', 'checkDenyEmail', -7, self::EXISTS_VALIDATE, 'callback'), //邮箱禁止注册
		array('femail', '', -8, self::EXISTS_VALIDATE, 'unique'), //邮箱被占用

		/* 验证手机号码 */
		array('fmobile', '//', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO:
		array('fmobile', 'checkDenyMobile', -10, self::EXISTS_VALIDATE, 'callback'), //手机禁止注册
		array('fmobile', '', -11, self::EXISTS_VALIDATE, 'unique'), //手机号被占用
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('fpassword', 'footstone_md5', self::MODEL_BOTH, 'function', FOOTSTONE_AUTH_KEY),
		array('fcreatetime', NOW_TIME, self::MODEL_INSERT),
		array('flastloginip', 'get_client_ip', self::MODEL_UPDATE, 'function', 1),
		array('flastmodifytime', NOW_TIME,self::MODEL_UPDATE),
		// array('fstatus', 'getStatus', self::MODEL_BOTH, 'callback'),
	);

	/**
	 * 检测用户代码是不是被禁止注册
	 * @param  string $fcode 用户代码
	 * @return boolean          ture - 未禁用，false - 禁止注册
	 */
	protected function checkDenyMember($fcode){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 检测邮箱是不是被禁止注册
	 * @param  string $email 邮箱
	 * @return boolean       ture - 未禁用，false - 禁止注册
	 */
	protected function checkDenyEmail($email){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 检测手机是不是被禁止注册
	 * @param  string $mobile 手机
	 * @return boolean        ture - 未禁用，false - 禁止注册
	 */
	protected function checkDenyMobile($mobile){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 根据配置指定用户状态
	 * @return integer 用户状态
	 */
	protected function getStatus(){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 注册一个新用户
	 * @param  string $fcode 用户代码
	 * @param  string $fname 用户名称
	 * @param  string $fpassword 用户密码
	 * @param  string $email    用户邮箱
	 * @param  string $mobile   用户手机号码
	 * @return integer          注册成功-用户信息，注册失败-错误编号
	 */
	public function register($fcode, $fname, $fpassword, $femail, $fmobile){
		$data = array(
			'fcode' => $fcode,
			'fname' => $fname,
			'fpassword' => $fpassword,
			'femail'    => $femail,
			'fmobile'   => $fmobile,
		);

		//验证手机
		if(empty($data['fmobile'])) unset($data['fmobile']);

		/* 添加用户 */
		if($this->create($data)){
			$fid = $this->add();
			return $fid ? $fid : 0; //0-未知错误，大于0-注册成功
		} else {
			return $this->getError(); //错误详情见自动验证注释
		}
	}

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
	 * @return integer           登录成功-用户ID，登录失败-错误编号
	 */
	public function login($username, $password, $type = 1){
		$map = array();
		switch ($type) {
			case 1:
				$map['fcode'] = $username;
				break;
			case 2:
				$map['femail'] = $username;
				break;
			case 3:
				$map['fmobile'] = $username;
				break;
			case 4:
				$map['fid'] = $username;
				break;
			default:
				return 0; //参数错误
		}
	
		/* 获取用户数据 */
		$user = $this->where($map)->find();
		if(is_array($user) && $user['fstatus']){
			/* 验证用户密码 ,在初始化的时候不需要验证管理员口令，*/
			if (((C('FOOTSTONE_ADMINISTRATOR_ALIVE')) && ($user['fid'] ==='1')) ||
			(footstone_md5($fpassword, FOOTSTONE_AUTH_KEY) === $user['fpassword'])){
	
				$this->updateLogin($user['fid']); //更新用户登录信息

				/* 记录session */
				$this->saveloginsession($user);
	
				return $user['fid']; //登录成功，返回用户ID
			} else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
	
	}

	/**
	 * 获取用户信息
	 * @param  string  $fcode 代码
	 * @param  integer $type     代码类型 （1-用户代码，2-邮箱，3-手机，4-UID）
	 * @return array                用户信息
	 */
	public function info($uid, $is_fcode = false){
		$map = array();
		switch ($type) {
			case 1:
				$map['fcode'] = $fcode;
				break;
			case 2:
				$map['femail'] = $fcode;
				break;
			case 3:
				$map['fmobile'] = $fcode;
				break;
			case 4:
				$map['fid'] = $fcode;
				break;
			default:
				return 0; //参数错误
		}


		$user = $this->where($map)->field('fid,fcode,fname,femail,fmobile,fstatus')->find();
		if(is_array($user) && $user['fstatus'] = 1){
			return array($user['fid'], $user['fcode'], $user['fname'], $user['femail'], $user['fmobile']);
		} else {
			return -1; //用户不存在或被禁用
		}
	}

	/**
	 * 检测用户信息
	 * @param  string  $field  用户代码
	 * @param  integer $type   用户代码类型 1-用户代码，2-用户邮箱，3-用户电话
	 * @return integer         错误编号
	 */
	public function checkField($field, $type = 1){
		$data = array();
		switch ($type) {
			case 1:
				$data['fcode'] = $field;
				break;
			case 2:
				$data['femail'] = $field;
				break;
			case 3:
				$data['fmobile'] = $field;
				break;
			default:
				return 0; //参数错误
		}

		return $this->create($data) ? 1 : $this->getError();
	}

	/**
	 * 更新用户登录信息
	 * @param  integer $uid 用户ID
	 */
	protected function updateLogin($fid){
		$data = array(
			'fid'              => $fid,
			'flastlogintime' => NOW_TIME,
			'flastloginip'   => get_client_ip(1),
		);
		$this->save($data);
	}

	/**
	 * 更新用户信息
	 * @param int $uid 用户id
	 * @param string $fpassword 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 * @author huajie <banhuajie@163.com>
	 */
	public function updateUserFields($fid, $fpassword, $data){
		if(empty($fid) || empty($fpassword) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}

		//更新前检查用户密码
		if(!$this->verifyUser($fid, $fpassword)){
			$this->error = '验证出错：密码不正确！';
			return false;
		}

		//更新用户信息
		$data = $this->create($data);
		if($data){
			return $this->where(array('fid'=>$uid))->save($data);
		}
		return false;
	}

	/**
	 * 验证用户密码
	 * @param int $uid 用户id
	 * @param string $fpassword_in 密码
	 * @return true 验证成功，false 验证失败
	 * @author huajie <banhuajie@163.com>
	 */
	protected function verifyUser($fid, $fpassword_in){
		$fpassword = $this->getFieldById($fid, 'fpassword');
		if(footstone_md5($fpassword_in, FOOTSTONE_AUTH_KEY) === $fpassword){
			return true;
		}
		return false;
	}
	
	/**
	 * 记录登录用户的session和cookie
	 * @param  array $user 用户信息数组
	 */
	private function saveloginsession($user){
		/* 记录登录SESSION和COOKIES */
		$auth = array(
				'fid'             => $user['fid'],
				'fcode'			=> $user['fcode'],
				'fname'        => $user['fname'],
				'flastlogintime' => $user['flastlogintime'],
		);
	
		session(C('FOOT_SESSION_USER_AUTH'), $auth);
		session(C('FOOT_SESSION_USER_AUTH_SIGN'), data_auth_sign($auth));
	
	}
	/**
	 * 注销当前用户
	 * @return void
	 */
	public function logout(){
		session(C('FOOT_SESSION_USER_AUTH'), null);
		session(C('FOOT_SESSION_USER_AUTH_SIGN'), null);
	}	
}
