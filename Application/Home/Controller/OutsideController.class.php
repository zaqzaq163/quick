<?php
namespace Home\Controller;
use Think\Controller;
class OutsideController extends Controller {
    public function keywordView(){
        $id = I('get.id');
        $kwd = M('keylist');
        $relation = C('PB_RELATION');
        $result = $kwd->where(array('id'=>$id))->field('word,list,lycos')->find();
        $info['lycos'] = $relation[$result['lycos']];
        $info['word'] = $result['word'];
        $list = (json_decode($result['list'],true));
        $this->assign('list',$list);
        $this->assign('info',$info);
        $this->display('info');
    }
}