<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function login(){
        if(IS_POST){
            $uid = I('post.i');
            $query = ['time'=>time(),'ip'=>sourceIP(),'uid'=>$uid];
            if(M('loginlog')->add($query)){
                $code = 0;
            }else{
                $code = -2;
            }
            $this->ajaxReturn($code);
        }else{
            $this->display();
        }
    }

    public function logout(){
        session('[destroy]');
        if(!session('userID')){
            $return = 0;
        }else{
            $return = -2;
        }
        $this->ajaxReturn($return);
    }

    public function check(){
        if(!IS_POST){
            $this->error('还有这种操作?');
        }
        $return = ['code'=>0,'msg'=>''];
        $user = M('admin');
        $info = I('post.');
        if($row = $user->where(['username'=>$info['u']])->find()){
            if($row['password'] == md5($info['p'])){
                session('userID',$info['u']);
                $return['data'] = $row['id'];
                $return['msg'] = '登陆成功';
            }else{
                $return['code'] = -2;
                $return['msg'] = '密码错误';
            }
        }else{
            $return['code'] = -2;
            $return['msg'] = '用户名不存在';
        }
        $this->ajaxReturn($return);
    }
}