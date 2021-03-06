<?php
namespace Home\Controller;
use Think\Controller;
class DeadlinkController extends CommonController {
    public function index(){
        $this->display();
    }

    public function _initialize()
    {
        session('[pause]');
        parent::_initialize();
    }

    public function startDead(){
        if(!IS_POST){
            $this->error('还有这种操作?');
        }
        vendor('phpQuery.phpQuery');
        $info = I('post.');
        $return = ['state'=>200,'msg'=>'','data'=>null];
        $wrl = preg_match('/^http/', $info['link']) ? $info['link'] : $info['type'].$info['link'];
        $html = Curl($wrl);
        $curl = parse_url($html['info']['url']);
        $link = M('link');

        if($html['code'] == 0){
            $pid = $link->add(array('time'=>time(),'url'=>$html['info']['url']));
            if(empty($pid)){
                $return['state'] = -1;
                $return['msg'] = '初始化检测失败';
            }else {
                \phpQuery::newDocumentHTML($html['data']['cont']);
                $list = pq('a[href]');
                $x = [];
                $res = [];
                foreach ($list as $k => $v) {
                    $dom = pq($v);
                    $href = $dom->attr('href');
                    $title = $dom->html();
                    $url = null;
                    if (pq($title)->is('img')) {
                        $title = pq($title)->attr('alt') ? pq($title)->attr('alt') : '图片链接';
                    } else {
                        $title = $dom->text();
                    }
                    $hrefArr = parse_url($href);

                    if ($hrefArr['host']) {
                        $url = $href;
                    } else {
                        switch (count($hrefArr)) {
                            case 1 :
                                if ($hrefArr['path']) {
                                    if (preg_match('/^\//', $hrefArr['path'])) {
                                        $url = $curl['scheme'] . "://" . $curl['host'] . $hrefArr['path'];
                                    } else if (preg_match('/^[a-z0-9]./i', $hrefArr['path'])) {
                                        $url = $curl['scheme'] . "://" . $curl['host'] . "/" . $hrefArr['path'];
                                    } else if (preg_match('/^\.\//', $hrefArr['path'])) {
                                        $url = $curl['scheme'] . "://" . $curl['host'] . "/" . $hrefArr['path'];
                                    } else {
                                        break;
                                    }
                                } else {
                                    break;
                                }
                                break;
                            case 2 :
                                if ($hrefArr['path'] && $hrefArr['query']) {
                                    if (preg_match('/^\//', $hrefArr['path'])) {
                                        $url = $curl['scheme'] . "://" . $curl['host'] . $hrefArr['path'] . "?" . $hrefArr['query'];
                                    } else if (preg_match('/^[a-z0-9]./i', $hrefArr['path'])) {
                                        $url = $curl['scheme'] . "://" . $curl['host'] . "/" . $hrefArr['path'] . "?" . $hrefArr['query'];
                                    } else if (preg_match('/^\.\//', $hrefArr['path'])) {
                                        $url = $curl['scheme'] . "://" . $curl['host'] . "/" . $hrefArr['path'] . "?" . $hrefArr['query'];
                                    } else {
                                        break;
                                    }
                                } else {
                                    break;
                                }
                                break;
                            default :
                                continue;
                        }
                    }

                    if ($url == null) continue;

                    if ($key = array_search($url, $x)) {
                        array_push($res[$key]['repeat']['info'], ['nofollow' => $dom->attr('rel') == 'nofollow' ? 1 : 0, 'title' => $title]);
                        $res[$key]['repeat']['num']++;
                        continue;
                    } else {
                        $x[$k] = $url;
                        $res[$k]['type'] = empty($hrefArr['host']) ? 1 : 2;
                        if (empty($hrefArr['host'])) {
                            $res[$k]['type'] = 1;
                        } else {
                            if ($curl['host'] == $hrefArr['host']) {
                                $res[$k]['type'] = 1;
                            } else {
                                $res[$k]['type'] = 2;
                            }
                        }
                        $res[$k]['nofollow'] = $dom->attr('rel') == 'nofollow' ? 1 : 0;
                        $res[$k]['url'] = $url;
                        $res[$k]['title'] = $title;
                        $res[$k]['repeat'] = ['info' => [], 'num' => 0];
                    }
                }

                $curAry = array_chunk($x, 50, true);
                foreach ($curAry as $k => $v) {
                    $result = multiCurl($v);
                    foreach ($result['data'] as $z => $y) {
                        if ($y['code'] == 0) {
                            $res[$z]['state'] = -1;
                        }else{
                            $error[$z] = $y['info']['url'];
                        }
                    }
                    if (!empty($error)) {
                        $errRes = multiCurl($error);
                        foreach ($errRes['data'] as $z => $y) {
                            if ($y['code'] == 0) {
                                $res[$z]['state'] = -1;
                            }else{
                                $res[$z]['state'] = $y['info']['http_code'];
                            }
                        }
                    }
                }

                foreach ($res as $k => &$v) {
                    $v['pid'] = $pid;
                    $v['info'] = json_encode($v['repeat']);
                    unset($v['repeat']);
                }
                if (empty($res)) {
                    $return['state'] = -2;
                    $return['msg'] = '该地址内无可用链接';
                } else {
                    $linkList = M('linklist');
                    if ($linkList->addAll($res)) {
                        $return['data'] = $pid;
                        $return['url'] = U('Export/DeadLink')."?pid=$pid";
                    } else {
                        $return['state'] = -3;
                        $return['msg'] = '检测结果储存数据库失败';
                    }
                }
            }
        }else{
            if($html['code'] == -200){
                $return['state'] = -200;
                $return['msg'] = '入口链接存在错误：'.$html['msg'];
            }else if($html['code'] == -404){
                $return['state'] = -404;
                $return['msg'] = '数据抓错取误,错误信息为：'.$html['msg'];
            }
        }
        $this->ajaxReturn($return);
    }

    public function history(){
        $this->display();
    }

    public function hisList(){
        $link = M('link');
        $return = ['code'=>0];
        if(IS_DELETE){
            if($id = I('get.id')){
                if(M('link')->delete($id)){
                    if(M('linklist')->where(['pid'=>$id])->delete()){
                        $return['code'] = 200;
                    }else{
                        $return['code'] = -2;
                    }
                }else{
                    $return['code'] = -2;
                }
            }else{
                $return['code'] = -4;
            }
        }else{
            if($page = I('post.page')){
                $limitNum = I('post.limit');
                $limit = ($page-1)*$limitNum.','.$limitNum;
            }else{
                $limit = 0;
            }
            $result = $link->limit($limit)->order(['id'=>'desc'])->select();
            foreach($result as $k=>&$v){
                $v['id'] = $v['id'] < 10 ? "0".$v['id'] : $v['id'];
                $v['time'] = date('Y-m-d H:i:s',$v['time']);
                $v['link']['infoLink'] = U('Deadlink/info',array('id'=>$v['id']));
                $v['link']['delLink'] = U('Deadlink/hisList',array('id'=>$v['id']));
                $v['link']['ExportLink'] = U('Export/DeadLink',array('id'=>$v['id']));
            }
            $return['count'] = $link->count();
            $return['data'] = $result;
        }
        $this->ajaxReturn($return);

    }

    public function info(){
        $link = M('link');
        if($id = I('get.id')){
            $row = $link->find($id);
            $this->assign('url',$row['url']);
            $this->display();
        }else{
            $this->error('还有这种操作?');
        };
    }
    public function infoLink(){
        $links = M('linklist');
        if($page = I('page')){
            $limitNum = I('limit');
            $limit = ($page-1)*$limitNum.','.$limitNum;
        }else{
            $limit = 0;
        }
        $result = $links->where(['pid'=>I('get.id')])->limit($limit)->order(['state'=>'desc'])->select();
        foreach($result as &$v){
            $v['info'] = json_decode($v['info'],true);
        }
        $return = ['code'=>0,'data'=>$result,'count'=>$links->where(['pid'=>I('get.id')])->count()];
        $this->ajaxReturn($return);
    }
}