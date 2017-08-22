<?php

function myCurl($url,$ip = false){
    session_write_close();
    $ch = curl_init();
    if($ip){
        $conf = $ip;
        curl_setopt($ch, CURLOPT_PROXY, $conf['ip']);
        switch($conf['type']){
            case 'Socks4':    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);break;
            case 'Socks5':    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);break;
            case 'Socks4/Socks5':    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);break;
            default:curl_setopt($ch,CURLOPT_PROXYTYPE, CURLPROXY_HTTP);break;
        }
    }
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)');// 3. 执行并获取HTML文档内容
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1); //重定向抓取
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    $output = curl_exec($ch);
    $httpInfo = curl_getinfo($ch);
    curl_close($ch);
    $return['state'] = $httpInfo['http_code'];
    $return['url'] = $httpInfo['url'];
    $return['cont'] = $output;
    return $return;
}

function getWait($lycos){
    switch ($lycos){
        case 1:$return = 100000;break;
        case 2:$return = 1000;break;
        case 4:$return = 1000;break;
        default:$return = 500000;
    }
    return $return;
}

function multiCurl($urls = array(),$field,$ip = false,$wait = 500000){

    session_write_close();
    $return = ['code'=>200];
    foreach($urls as $k=>$url) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        if($ip != 'false'){
            curl_setopt($ch,CURLOPT_PROXY, $ip);
            curl_setopt($ch,CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "3igrkmx3bu:fo2gpnt9s3");
        }
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1); //重定向抓取
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_TIMEOUT,60);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));
        curl_setopt($ch,CURLOPT_ENCODING, "gzip");
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        if(curl_error($ch)){
            $return['error'][] = $k;
            file_put_contents('d:/file_put/error'.rand().rand().".txt",curl_error($ch));
        }
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        foreach($field as $v){
            if($v == 'cont'){
                $return['data'][$k]['cont'] = $content;
            }else{
                $return['data'][$k][$v] = $info[$v];
            }
        }
        usleep($wait*rand(1,5));
    }
    if(empty($return['data'])){
        $return['code'] = -2;
    }
    return $return;

    /* $handle = array();
     $mh = curl_multi_init();
     $active = null;
     foreach($urls as $k=>$url) {
         $ch = curl_init();
         curl_setopt($ch,CURLOPT_URL,$url);
         if($ip != 'false'){
             curl_setopt($ch,CURLOPT_PROXY, $ip);
             curl_setopt($ch,CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
             curl_setopt($ch, CURLOPT_PROXYUSERPWD, "3igrkmx3bu:fo2gpnt9s3");
         }
         curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36');
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         curl_setopt($ch,CURLOPT_HEADER,0);
         curl_setopt($ch,CURLOPT_TIMEOUT,60);
         curl_setopt($ch,CURLOPT_HTTPHEADER, array('Accept-Encoding:gzip'));
         curl_setopt($ch,CURLOPT_ENCODING, "gzip");
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
         curl_multi_add_handle($mh, $ch);
         $handle[$k] = $ch;
     }

     do {
         usleep($wait*rand(1,8));
         $mrc = curl_multi_exec($mh, $active);
     } while ($mrc == CURLM_CALL_MULTI_PERFORM);

     while ($active and $mrc == CURLM_OK) {

         if(curl_multi_select($mh) === -1){
             usleep(100);
         }
         do {
             $mrc = curl_multi_exec($mh, $active);
         } while ($mrc == CURLM_CALL_MULTI_PERFORM);

     }

     foreach($handle as $i => $ch) {
         if(curl_error($ch)){
             $data['error'][] = $i;
             file_put_contents('d:/file_put/error'.rand().rand().".txt",curl_error($ch));
         }
         $content  = curl_multi_getcontent($ch);
         $info = curl_getinfo($ch);
         foreach($field as $k=>$v){
             if($v == 'cont'){
                 $data[$i]['cont'] = $content;
             }else{
                 $data[$i][$v] = $info[$v];
             }
         }
     }
     foreach($handle as $ch) {
         curl_multi_remove_handle($mh, $ch);
     }
     curl_multi_close($mh);
     return $data;*/
}

function testIp($ip){
    session_write_close();

    $urls = [
        'https://www.baidu.com/s?wd=1',
        'https://m.baidu.com/s?word=1',
        'https://www.sogou.com/web?query=1',
        'http://so.m.sm.cn/s?q=1',
        'https://www.so.com/s?q=1'
    ];

    $handle = array();
    $mh = curl_multi_init();
    $return = ['code'=>200,'msg'=>null,'data'=>null];
    foreach($urls as $k=>$url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_PROXY, $ip);
        curl_setopt($ch,CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_multi_add_handle($mh, $ch);
        $handle[$k] = $ch;
    }
    $active = null;

    do {
        curl_multi_exec($mh, $active);
    } while ($active > 0);


    foreach($handle as $i => $ch) {
        $info = curl_getinfo($ch);
        if($info['http_code'] != 200){
            $return['code'] = -2;
        }
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    return $return;
}


function CoverCondition($condition){
    session_write_close();

    $arr = explode(',',str_replace("\n", ',', str_replace('|',',',str_replace('，',',',$condition))));
    $result = [];
    foreach($arr as $k=>$v){
        if($v == '' || $v == ' '){
            continue;
        }else if(in_array($v,$result)){
            continue;
        }else{
            $result[] = $v;
        };
    }
    return $result;
}

function getBaseDomain($url='',$host = false){
    session_write_close();

    if(!$url){
        return $url;
    }
    $state_domain = array(
        'al','dz','af','ar','ae','aw','om','az','eg','et','ie','ee','ad','ao','ai','ag','at','au','mo','bb','pg','bs','pk','py','ps','bh','pa','br','by','bm','bg','mp','bj','be','is','pr','ba','pl','bo','bz','bw','bt','bf','bi','bv','kp','gq','dk','de','tl','tp','tg','dm','do','ru','ec','er','fr','fo','pf','gf','tf','va','ph','fj','fi','cv','fk','gm','cg','cd','co','cr','gg','gd','gl','ge','cu','gp','gu','gy','kz','ht','kr','nl','an','hm','hn','ki','dj','kg','gn','gw','ca','gh','ga','kh','cz','zw','cm','qa','ky','km','ci','kw','cc','hr','ke','ck','lv','ls','la','lb','lt','lr','ly','li','re','lu','rw','ro','mg','im','mv','mt','mw','my','ml','mk','mh','mq','yt','mu','mr','us','um','as','vi','mn','ms','bd','pe','fm','mm','md','ma','mc','mz','mx','nr','np','ni','ne','ng','nu','no','nf','na','za','aq','gs','eu','pw','pn','pt','jp','se','ch','sv','ws','yu','sl','sn','cy','sc','sa','cx','st','sh','kn','lc','sm','pm','vc','lk','sk','si','sj','sz','sd','sr','sb','so','tj','tw','th','tz','to','tc','tt','tn','tv','tr','tm','tk','wf','vu','gt','ve','bn','ug','ua','uy','uz','es','eh','gr','hk','sg','nc','nz','hu','sy','jm','am','ac','ye','iq','ir','il','it','in','id','uk','vg','io','jo','vn','zm','je','td','gi','cl','cf','cn','yr','com','arpa','edu','gov','int','mil','net','org','biz','info','pro','name','museum','coop','aero','xxx','idv','me','mobi','asia','ax','bl','bq','cat','cw','gb','jobs','mf','rs','su','sx','tel','travel','love'
    );

    if(!preg_match("/^http/is", $url)){
        $url="http://".$url;
    }

    $res = null;
    $res->domain = null;
    $res->host = null;
    $url_parse = parse_url(strtolower($url));
    $urlarr = explode(".", $url_parse['host']);
    $count = count($urlarr);
    if($host){
        preg_match("/[a-z0-9]+\.[a-z0-9]+[\.]*[a-z0-9]*[\.]*[a-z0-9]*/", $url_parse['host'], $matches);
        $res->domain = $matches[0];
    }else if($count <= 2){
        #当域名直接根形式不存在host部分直接输出
        $res->domain = $url_parse['host'];
    }elseif($count > 2){
        $last = array_pop($urlarr);
        $last_1 = array_pop($urlarr);
        $last_2 = array_pop($urlarr);

        $res->domain = $last_1.'.'.$last;
        $res->host = $last_2;

        if(in_array($last, $state_domain)){
            $res->domain=$last_1.'.'.$last;
            $res->host=implode('.', $urlarr);
        }

        if(in_array($last_1, $state_domain)){
            $res->domain = $last_2.'.'.$last_1.'.'.$last;
            $res->host = implode('.', $urlarr);
        }
        #print_r(get_defined_vars());die;
    }
    return $res;
}