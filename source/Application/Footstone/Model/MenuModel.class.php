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
/* * 
 * 菜单管理模型
 */
class MenuModel extends Model {

    //自动验证
    protected $_validate = array(
        array('url','require','url必须填写'), //默认情况下用正则进行验证
        
    );

    //自动完成
    protected $_auto = array(
            //array(填充字段,填充内容,填充条件,附加规则)
    );


    /**
     * 按父ID查找菜单子项
     * @param integer $parentid   父菜单ID  
     * @param integer $with_self  是否包括他自己
     */
    public function admin_menu($parentid, $with_self = false) {
        //父节点ID
        $parentid = (int) $parentid;
        $result = $this->where(array('fpid' => $parentid, 'fstatus' => 1))->order(array("fsort" => "ASC"))->select();
        if ($with_self) {
            $result2[] = $this->where(array('fid' => $parentid))->find();
            $result = array_merge($result2, $result);
        }
        return $array;
    }



    /**
     * 按父ID查找下级子菜单
     * @param integer $parentid   父菜单ID  
     * @param integer $with_self  是否包括他自己
     * 按数组方式返回；
     */
    public function NextLevelMenu($parentid, $with_self = false) {
        //父节点ID
        $parentid = (int) $parentid;
        $result = $this->where(array('fpid' => $parentid, 'fstatus' => 1))->order(array("fsort" => "ASC"))->select();
        
        if ($with_self) {
            $result2[0] = $this->where(array('fid' => $parentid))->find();
            $result = array_merge($result2, $result);
        }

        return $result;
    }

    /**
     * 按父ID查找所有的子菜单项
     * @param integer $parentid   父菜单ID  
     * 按数组方式返回；
     */
    public function SubMenu($parentid){
        $result = $this->NextLevelMenu($parentid);

        if (count($result)<>0) {
            foreach ($result as $key => $value) {
                $result[$key]['items'] = $this->SubMenu($value['fid']);
            }
        }
        return $result;
    }

    /**
     * 获取一级菜单 头部菜单导航
     * 
     */
    public function MainMenu() {
        $array = $this->NextLevelMenu(0, 0);
        return $array;
    }
    /**
     * 获取整个菜单树
     *
     */
    public function AllMenu() {
        $result = $this->SubMenu(0);
        return $result;
        //父节点ID
        // $parentid = (int) $parentid;
        // $result = $this->where(array('pid' => $parentid, 'hide' => 0))->order(array("sort" => "ASC"))->select();
        // if ($with_self) {
        //     $result2[] = $this->where(array('id' => $parentid))->find();
        //     $result = array_merge($result2, $result);
        // }
        // return $array;
    }




    /**
     * 更新缓存
     * @param type $data
     * @return type
     */
    public function menu_cache($data = null) {
        if (empty($data)) {
            $data = $this->select();
            F("Menu", $data);
        } else {
            F("Menu", $data);
        }
        return $data;
    }

    /**
     * 后台有更新/编辑则删除缓存
     * @param type $data
     */
    public function _before_write(&$data) {
        parent::_before_write($data);
        F("Menu", NULL);
    }

    //删除操作时删除缓存
    public function _after_delete($data, $options) {
        parent::_after_delete($data, $options);
        F("Menu", NULL);
    }
    


}

?>