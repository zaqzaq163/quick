<?php
namespace Home\Controller;
use Think\Controller;
class ToolsController extends CommonController {

    public function viewIp(){
        $ip = M('ip');
        $list = $ip->select();
        foreach($list as $k=>&$v){
            $v['link']['use'] = U('Tools/controlIp',array('id'=>$v['id'],'type'=>1));
            $v['link']['unUse'] = U('Tools/controlIp',array('id'=>$v['id'],'type'=>0));
            $v['link']['del'] = $v['id'];
        }
        $this->ajaxReturn(array('data'=>$list));
    }

    public function controlIp(){
        $ip = M('ip');
        if(IS_DELETE){
            $id = I('put.id');
            if($ip->delete($id)){
                $this->ajaxReturn(200);
            }else{
                $this->ajaxReturn(-2);
            }
        }else if(IS_POST){
            $id = I('post.id');
            $type = I('post.type');
            $data['is_use'] = $type;
            if($ip->where(array('id'=>$id))->save($data)){
                $this->ajaxReturn(200);
            }else{
                $this->ajaxReturn(-2);
            }
        }else{
            $this->ajaxReturn(-3);
        }
    }

    public function ip(){
        $this->display();
    }

    public function getIp(){
        $state = IpApi(100,false);
        $this->ajaxReturn($state);
    }

    public function DeleteIp(){
        $return = ['code'=>301,'msg'=>null];
        $step = I('post.step');
        $ip = M('ip');
        $arr = [];
        $list = $ip->where(array('is_use'=>0,'id'=>array('GT',$step)))->limit(100)->order('id asc')->select();
        foreach($list as $k=>$v){
            $arr[$v['id']] = ['ip:port'=>$v['ip'],'http_type'=>$v['type']];
        }
        if(!$list){
            $return['code'] = 200;
        }else{
            $ids = testIp($arr,false);
            if($id = $ip->where(array('id'=>array('in',$ids['data']['error'])))->delete()){
                $return['code'] = 301;
                $return['step'] = end($list['id']);
            }else{
                $return['code'] = -2;
                $return['msg'] = '删除失败了/(ㄒoㄒ)/~~';
            };
        }
        $this->ajaxReturn($return);
    }
}