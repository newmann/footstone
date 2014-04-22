<?php
// +----------------------------------------------------------------------
// | Footstone [ WE CAN DO MORE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Newmannhu <Newmannhu@qq.com> <http://>
// +----------------------------------------------------------------------

namespace Footstone\Controller;



class UserController extends FootstoneController{
    /**
     * 构造方法，实例化操作模型
     */
    protected function _init(){
        $this->model = new UserModel();
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function add($usercode, $username, $password, $email, $mobile = ''){
        return $this->model->register($usercode,$username, $password, $email, $mobile);
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

		/* 获取用户数据 */
		$user = $this->model->where($map)->find();
		if(is_array($user) && $user['fstatus']){
			/* 验证用户密码 ,在初始化的时候不需要验证管理员口令，*/
			if (((FOOTSTONE_ADMINISTRATOR_ALIVE) && ($user['fid'] ===1)) ||
				(footstone_md5($fpassword, FOOTSTONE_AUTH_KEY) === $user['fpassword'])){
				
				$this->model->updateLogin($user['fid']); //更新用户登录信息
				//记录行为
				action_log('user_login', 'member', $uid, $uid);
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
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }

    /**
     * 检测用户
     * @param  string  $field  用户名
     * @return integer         错误编号
     */
    public function checkUsername($username){
        return $this->model->checkField($username, 1);
    }

    /**
     * 检测邮箱
     * @param  string  $email  邮箱
     * @return integer         错误编号
     */
    public function checkEmail($email){
        return $this->model->checkField($email, 2);
    }

    /**
     * 检测手机
     * @param  string  $mobile  手机
     * @return integer         错误编号
     */
    public function checkMobile($mobile){
        return $this->model->checkField($mobile, 3);
    }

    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author 
     */
    public function updateInfo($uid, $password, $data){
        if($this->model->updateUserFields($uid, $password, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }
    /**
     * 用户管理首页
     * @author Newmannhu<newmannhu@qq.com>
     */
    public function index(){
//     	$nickname       =   I('nickname');
//     	$map['status']  =   array('egt',0);
//     	if(is_numeric($nickname)){
//     		$map['uid|nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
//     	}else{
//     		$map['nickname']    =   array('like', '%'.(string)$nickname.'%');
//     	}
    	$map['fstatus']  =   array('egt',0);
    	$list   = $this->lists('User', $map);
//     	int_to_string($list);
    	$this->assign('_list', $list);
    	$this->display();
    }
    
//     /**
//      * 修改昵称初始化
//      * @author Newmannhu <newmannhu@qq.com>
//      */
//     public function updateNickname(){
//     	$nickname = M('Member')->getFieldByUid(UID, 'nickname');
//     	$this->assign('nickname', $nickname);
//     	$this->meta_title = '修改昵称';
//     	$this->display();
//     }
    
//     /**
//      * 修改昵称提交
//      * @author Newmannhu <newmannhu@qq.com>
//      */
//     public function submitNickname(){
//     	//获取参数
//     	$nickname = I('post.nickname');
//     	$password = I('post.password');
//     	empty($nickname) && $this->error('请输入昵称');
//     	empty($password) && $this->error('请输入密码');
    
//     	//密码验证
//     	$User   =   new UserApi();
//     	$uid    =   $User->login(UID, $password, 4);
//     	($uid == -2) && $this->error('密码不正确');
    
//     	$Member =   D('Member');
//     	$data   =   $Member->create(array('nickname'=>$nickname));
//     	if(!$data){
//     		$this->error($Member->getError());
//     	}
    
//     	$res = $Member->where(array('uid'=>$uid))->save($data);
    
//     	if($res){
//     		$user               =   session('user_auth');
//     		$user['username']   =   $data['nickname'];
//     		session('user_auth', $user);
//     		session('user_auth_sign', data_auth_sign($user));
//     		$this->success('修改昵称成功！');
//     	}else{
//     		$this->error('修改昵称失败！');
//     	}
//     }
    
    /**
     * 修改密码初始化
     * @author Newmannhu <newmannhu@qq.com>
     */
    public function updatePassword(){
//     	$this->meta_title = '修改密码';
    	$this->display();
    }
    
    /**
     * 修改密码提交
     * @author Newmannhu <newmannhu@qq.com>
     */
    public function submitPassword(){
    	//获取参数
    	$password   =   I('post.old');
    	empty($password) && $this->error('请输入原密码');
    	$data['password'] = I('post.password');
    	empty($data['password']) && $this->error('请输入新密码');
    	$repassword = I('post.repassword');
    	empty($repassword) && $this->error('请输入确认密码');
    
    	if($data['password'] !== $repassword){
    		$this->error('您输入的新密码与确认密码不一致');
    	}
    
    	$res    =   $this->model->updateInfo(UID, $password, $data);
    	if($res['status']){
    		$this->success('修改密码成功！');
    	}else{
    		$this->error($res['info']);
    	}
    }
    
//     /**
//      * 用户行为列表
//      * @author Newmannhu <newmannhu@qq.com>
//      */
//     public function action(){
//     	//获取列表数据
//     	$Action =   M('Action')->where(array('status'=>array('gt',-1)));
//     	$list   =   $this->lists($Action);
//     	int_to_string($list);
//     	// 记录当前列表页的cookie
//     	Cookie('__forward__',$_SERVER['REQUEST_URI']);
    
//     	$this->assign('_list', $list);
//     	$this->meta_title = '用户行为';
//     	$this->display();
//     }
    
//     /**
//      * 新增行为
//      * @author Newmannhu <newmannhu@qq.com>
//      */
//     public function addAction(){
//     	$this->meta_title = '新增行为';
//     	$this->assign('data',null);
//     	$this->display('editaction');
//     }
    
//     /**
//      * 编辑行为
//      * @author Newmannhu <newmannhu@qq.com>
//      */
//     public function editAction(){
//     	$id = I('get.id');
//     	empty($id) && $this->error('参数不能为空！');
//     	$data = M('Action')->field(true)->find($id);
    
//     	$this->assign('data',$data);
//     	$this->meta_title = '编辑行为';
//     	$this->display();
//     }
    
//     /**
//      * 更新行为
//      * @author Newmannhu <newmannhu@qq.com>
//      */
//     public function saveAction(){
//     	$res = D('Action')->update();
//     	if(!$res){
//     		$this->error(D('Action')->getError());
//     	}else{
//     		$this->success($res['id']?'更新成功！':'新增成功！', Cookie('__forward__'));
//     	}
//     }
    
    /**
     * 用户状态修改
     * @author Newmannhu<Newmammhu@qq.com>
     */
    public function changeStatus($method=null){
    	$id = array_unique((array)I('id',0));
    	if( in_array(C('USER_ADMINISTRATOR'), $id)){
    		$this->error("不允许对超级管理员执行该操作!");
    	}
    	$id = is_array($id) ? implode(',',$id) : $id;
    	if ( empty($id) ) {
    		$this->error('请选择要操作的数据!');
    	}
    	$map['uid'] =   array('in',$id);
    	switch ( strtolower($method) ){
    		case 'forbiduser':
    			$this->forbid('User', $map );
    			break;
    		case 'resumeuser':
    			$this->resume('User', $map );
    			break;
    		case 'deleteuser':
    			$this->delete('User', $map );
    			break;
    		default:
    			$this->error('参数非法');
    	}
    }
    
    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
    	switch ($code) {
    		case -1:  $error = '用户名长度必须在16个字符以内！'; break;
    		case -2:  $error = '用户名被禁止注册！'; break;
    		case -3:  $error = '用户名被占用！'; break;
    		case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
    		case -5:  $error = '邮箱格式不正确！'; break;
    		case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
    		case -7:  $error = '邮箱被禁止注册！'; break;
    		case -8:  $error = '邮箱被占用！'; break;
    		case -9:  $error = '手机格式不正确！'; break;
    		case -10: $error = '手机被禁止注册！'; break;
    		case -11: $error = '手机号被占用！'; break;
    		default:  $error = '未知错误';
    	}
    	return $error;
    }
    
   
}
