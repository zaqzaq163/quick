<?php
namespace Home\Controller;
use Think\Controller;
class KwdController extends CommonController {
    public $getConf = array(
        1=>array(//百度
            'searchUrl'=>'https://www.baidu.com/s?by=submit&wd=',
            'totalClass'=>'.c-container',
        ),
        2=>array(//手机百度
            'searchUrl'=>'https://m.baidu.com/s?word=',
            'totalClass'=>'.c-result',
        ),
        3=>array(//搜狗
            'searchUrl'=>'https://www.sogou.com/web?query=',
            'totalClass'=>'.results > .vrwrap,.results > .rb',
        ),
        4=>array(//神马
            'searchUrl'=>'http://so.m.sm.cn/s?q=',
            'totalClass'=>'.ali_row',
        ),
        5=>array(//360
            'searchUrl'=>'https://www.so.com/s?ie=utf-8&fr=none&src=360sou_newhome&q=',
            'totalClass'=>'.res-list',
        )
    );
    public function kwdMake($keyword,$lycos){
        if(in_array($lycos,array(1,2,4)) ){
            $res = str_replace('+', "%20", urlencode($keyword));
        }else{
            $res = urlencode($keyword);
        }
        return $res;
    }
    public function _initialize(){
        session('[pause]');
    }
    public function index(){
        $this->display();
    }
    public $errMsg = ['0'=>'成功','10001'=>'出现验证码','10002'=>'连接超时','10003'=>'目标状态异常','10004'=>'(本地/代理)网络异常','10010'=>'未知异常'];
    public function history(){
        $this->display();
    }
    public function hisList(){
        $coverMod = M('cover');
        $return = ['code'=>0];
        if(IS_DELETE){
            $id = I('get.id');
            if($coverMod->delete($id)){
                $keyMod = M('keyword');
                $ids = array_column($keyMod->field('id')->where(['pid'=>$id])->select(), 'id');
                if($keyMod->where(['id'=>['in',$ids]])->delete()){
                    if(!M('keylist')->where(['pid'=>['in',$ids]])->delete()){
                        $return['code'] = -2;
                        $return['msg'] = '删除失败';
                    }
                }else{
                    $return['code'] = -2;
                    $return['msg'] = '删除失败';
                };
            }else{
                $return['code'] = -2;
                $return['msg'] = '删除失败';
            }
        }else{
            if($page = I('post.page')){
                $limitNum = I('post.limit');
                $limit = ($page-1)*$limitNum.','.$limitNum;
            }else{
                $limit = 0;
            }
            $list = $coverMod->limit($limit)->order(['id'=>'desc'])->select();
            foreach($list as $k=>&$v){
                $v['link']['infoLink'] = U('Kwd/view',array('id'=>$v['id']));
                $v['link']['delLink'] = U('Kwd/hisList',array('id'=>$v['id']));
                $v['link']['ExportLink'] = U('Export/keyword',array('id'=>$v['id']));


                if($v['type'] == 1){
                    $keywords = M('keyword')->getFieldByPid($v['id'],'keywords');
                    $keywords = explode(',',$keywords);
                    if(count($keywords)>5){
                        $v['content'] = "【<span class='tips'>".$keywords[0]."</span>】等".count($keywords)."个关键词";
                    }else{
                        $v['content'] = $keywords;
                    }
                    $v['type'] = '输入';
                }else if($v['type'] == 2){
                    $v['content'] = '文件名为【<span class="tips">'.$v['file'].'</span>】';
                    $v['type'] = '导入';
                }else{
                    $return['error'][] = $v['id']."←  这个id有着令人窒息的操作";
                }

                $v['time'] = date('Y-m-d H:i:s',$v['time']);
                $v['ip'] = $v['ip'] == 0 ? '本机' : $v['ip'];
            }
            $return['data'] = $list;
            $return['count'] = count($list);
        }
        $this->ajaxReturn($return);
    }
    public function view(){
        $cover = M('cover');
        $keyword = M('keyword');
        $kwd = M('keylist');
        $lid = I('get.id');
        $coverData = $cover->find($lid);
        $type = $coverData['type'];
        $temp = $type == 1 ? 'normal' : 'export';
        switch ($type){
            case 1 :
                $pid = $keyword->getFieldByPid($coverData['id'],'id');
                $result = $kwd->field('list',true)->where(array('pid'=>$pid))->order('lycos,id')->select();
                $relation = C('PB_RELATION');
                foreach($result as $k=>$v){
                    $v['msg'] = $this->errMsg[$v['state']];
                    $arr[$relation[$v['lycos']]][] = $v;
                }
                break;
            case 2 :
                $pid = $keyword->where(['pid'=>$coverData['id']])->field('id,conditions')->select();
                $relation = C('PB_RELATION');
                foreach($pid as $val){
                    $res = $kwd->field('list',true)->where(array('pid'=>$val['id']))->order('lycos,id')->select();
                    foreach($res as $k=>&$v){
                        $v['msg'] = $this->errMsg[$v['state']];
                        $arr[$relation[$v['lycos']]][$val['conditions']][] = $v;
                    }
                }
                ;break;
            default:break;
        }
        $this->assign('arr',$arr);
        $this->display($temp);
    }

/*
 * 错误代码  10001   抓取页面出现验证码
 * 错误代码  10002   响应时间超过60秒
 * 错误代码  10003   目标网页错误的状态码
 * 错误代码  10004   代理IP连接失败
 */

    public function getSource(){
        if(!IS_POST){
            $this->error('还有这种操作?');
        }
        $abc = I('post.');
        $keywords = $abc['keywords'];$pid = $abc['pid'];$ip = $abc['ip'];$con_array = $abc['conditions'];$lycos = $abc['lycos'];
        Vendor('phpQuery.phpQuery');
        $return = ['state'=>200,'msg'=>null];
        $urls = [];
        foreach($keywords as $v) {
            $urls[$v] = $this->getConf[$lycos]['searchUrl'] . $this->kwdMake($v, $lycos);
        }
        $outputs = Curls($urls,$ip,$lycos);
        foreach($outputs as $k=>$output){

            $sql = ['pid' => $pid, 'lycos' => $lycos, 'word' => $k , 'list'=>'error' , 'ranknum'=>0 , 'rankcondition'=>''];

            if(!empty($output['data'])) {
                $total = 0;
                $order = array();
                $res = array();
                \phpQuery::newDocumentHTML($output['data']['cont']);
                $list = pq($this->getConf[$lycos]['totalClass']);
                foreach ($list as $key => $val) {
                    $dom = pq($val);
                    $row = array('id' => $key + 1);
                    switch ($lycos) {
                        case 1:
                            $row['title'] = $dom->find('.t')->text();
                            $row['url'] = getBaseDomain($dom->find('.g,.c-showurl')->text(), true)->domain;
                            break;
                        case 2:
                            $row['title'] = $dom->find('.c-title ')->text();
                            $row['url'] = getBaseDomain(json_decode(str_replace('\'', '"', $dom->attr('data-log')))->mu, true)->domain;
                            break;
                        case 3:
                            $row['title'] = $dom->find('.vrTitle,.vrt,.pt')->text();
                            $row['url'] = getBaseDomain($dom->find('cite')->text(), true)->domain;
                            break;
                        case 4:
                            $row['title'] = $dom->find('a')->text();
                            $row['url'] = getBaseDomain($dom->find('.g,.other')->text(), true)->domain;
                            break;
                        case 5:
                            $row['title'] = $dom->find('.res-title,.title ')->text();
                            $row['url'] = getBaseDomain($dom->find('cite,.url')->text(), true)->domain;
                            break;
                        default :
                            ;
                    }
                    foreach ($con_array as $vv) {
                        if ($vv != '' || $vv != null || !empty($vv)) {
                            if (getBaseDomain($vv)->domain === getBaseDomain($row['url'])->domain) {
                                $row['mate_word'] .= $vv;
                                $order[] = $row['id'];
                                $total++;
                            }
                        }
                    }
                    $res[] = $row;
                }
                if (empty($res)) {
                    $sql['state'] = 10001;
                } else {
                    $sql['ranknum'] = $total;
                    $sql['list'] = json_encode($res);
                    $sql['rankcondition'] = implode(",", $order);
                    $sql['state'] = 0;
                }
            }else{
                if($output['error']['type'] == -200){
                    $sql['state'] = 10003;
                }else if($output['error']['type'] == -404){
                    switch($output['error']['state']){
                        case 28:$sql['state'] = 10002;break;
                        case 56:$sql['state'] = 10004;break;
                        default : $sql['state'] = 10010 ;
                    }
                }else{
                    $sql['state'] = 10010;
                }
            }
            $query[] = $sql;
        }
        if(!empty($query)){
            M('Keylist')->addAll($query);
        }
        $this->ajaxReturn($return);
    }


    public function getIp(){
        if(!IS_POST){
            $this->error('还有这种操作?');
        }
        $return['code'] = 0;
        if(I('post.use')){
            $return['data'] = '113.209.100.170:23128';
        }

        sleep(0);
        $this->ajaxReturn($return);
    }
    public function Condition(){
        if(!IS_POST){
            $this->error('还有这种操作?');
        }
        $data = I('post.');
        $return = ['state'=>200,'msg'=>null,'data'=>null];
        $con_array = CoverCondition($data['data']['conditions']);
        $return['data']['conditions'] = $con_array;
        if($data['type'] == 'normal'){
            $ppid = M('cover')->add(['type'=>'1','time'=>time()]);
            $return['data']['ppid'] = $ppid;
            $kwd_array = CoverCondition($data['data']['keywords']);
            $return['count'] = count($kwd_array);
        }else{
            $ppid = I('post.pid');
            $kwd_array = $data['data']['keywords'];
        }
        if(count($kwd_array)>50) {
            $return['data']['keywords'] = array_chunk($kwd_array, 50);
        }else{
            $return['data']['keywords'][] = $kwd_array;
        }
        $kwd = M('Keyword');
        if($pid = $kwd->add(array('pid'=>$ppid,'keywords'=>implode(',',$kwd_array),'conditions'=>implode(',',$con_array)))){
            $return['data']['pid'] = $pid;
        }else{
            $return['state'] = -2;
        }
        $this->ajaxReturn($return);
    }
}
