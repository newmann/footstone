<?php
// +----------------------------------------------------------------------
// | Footstoen [ WE CAN DO MORE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://  All rights reserved.
// +----------------------------------------------------------------------
// | Author: Newmannhu <Newmannhu@qq.com> <http://>
// +----------------------------------------------------------------------
namespace Footstone\Controller;

/**
 * Footstone后台首页
 * @author Newmannhu <Newmannhu@qq.com>
 */

class IndexController extends FootstoneController {
	
	
	function _initialize() {
		parent::_initialize();
		//$this->initMenu();

	}
    //后台框架首页
    public function index() {

        //$this->assign("SUBMENU_CONFIG", json_encode(D("Menu")->menu_json()));
    	$AllMenu = D("Menu")->AllMenu();
    	

		$this->assign("SUBMENU_CONFIG", json_encode($AllMenu)); 


       	$this->display();
        
    }

    

}

?>
