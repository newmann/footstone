<?php
// +----------------------------------------------------------------------
// | Footstoen [ WE CAN DO MORE ]
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
        $result = $this->where(array('pid' => $parentid, 'hide' => 0))->order(array("sort" => "ASC"))->select();
        if ($with_self) {
            $result2[] = $this->where(array('id' => $parentid))->find();
            $result = array_merge($result2, $result);
        }
        return $array;
    }

    /** 菜单数组定义
     array(
                "id" => "",
                "title" => "changyong",
                "pid" => "常用",
                "sort" => "",
                "url" => "",
                "tip" => "",
                "group" => "",
                "itmes" => $items(子菜单)
        );
        整个菜单树是由数组嵌套而成，一级菜单+二级菜单+3级菜单
        菜单已经按sort方式排好序；
    */


    /**
     * 按父ID查找下级子菜单
     * @param integer $parentid   父菜单ID  
     * @param integer $with_self  是否包括他自己
     * 按数组方式返回；
     */
    public function NextLevelMenu($parentid, $with_self = false) {
        //父节点ID
        $parentid = (int) $parentid;
        $result = $this->where(array('pid' => $parentid, 'hide' => 0))->order(array("sort" => "ASC"))->select();
        
        if ($with_self) {
            $result2[0] = $this->where(array('id' => $parentid))->find();
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
        // if (count($result)<>0) {
        //     foreach ($result as $key => $value) {
        //         $value['items'] = $this->SubMenu($value['id']);
        //     }
        // }
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



    //取得树形结构的菜单
    public function get_tree($myid, $parent = "", $Level = 1) {
        $data = $this->admin_menu($myid);
        $Level++;
        if (is_array($data)) {
            foreach ($data as $a) {
                $id = $a['id'];
                $name = ucwords($a['app']);
                $model = ucwords($a['model']);
                $action = $a['action'];
                //附带参数
              $fu = "";
                if ($a['data']) {
                    $fu = "?" . $a['data'];
                }
                $array = array(
                    "icon" => $a['icon'],
                    "id" => $id . $name,
                    "name" => $a['name'],
                    "parent" => $parent,
                    "url" => U("{$name}/{$model}/{$action}{$fu}", array("menuid" => $id)),
                ); 
                
                
                
                $ret[$id . $name] = $array;
                $child = $this->get_tree($a['id'], $id, $Level);
                //由于后台管理界面只支持三层，超出的不层级的不显示
                if ($child && $Level <= 3) {
                    $ret[$id . $name]['items'] = $child;
                }
               
            }
            return $ret;
        }
       
        return false;
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
        $this->_before_write($data);
    }
    


}

?>