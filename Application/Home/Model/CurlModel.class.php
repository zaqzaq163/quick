<?php
namespace Home\Model;
use Think\Model;
class CurlModel extends Model {
    public function _initialize(){
        session_write_close();
    }
    public function OneCurl($url){

    }
    public function TwoCurl(){

    }
    public function multiCurl(){

    }
}