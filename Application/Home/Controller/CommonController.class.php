<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
    public function _initialize(){
        if(!session('?userID')){
            $this->redirect('Home/Login/login');
        }
    }
}