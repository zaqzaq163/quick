<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function login(){
        $this->display();
    }


    public function check(){
        session('userID' ,I('post.UserName'));
        $this->success('登陆成功',U('Home/Index/index'));
    }
}