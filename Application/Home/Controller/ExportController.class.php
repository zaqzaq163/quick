<?php
namespace Home\Controller;
use Think\Controller;
class ExportController extends CommonController {
    public $link_style_array = [
        'font'  => [
            'color' => ['rgb' => '0000FF'],
            'underline' => 'single'
        ]
    ];

    public function keyword(){
        $lid = I('get.id');
        Vendor("phpExcel.PHPExcel");
        $excel =  new \PHPExcel();
        $coverMod = M('cover')->find($lid);
        $type = $coverMod['type'];
        $keyObject = M('keyword');
        if($type == 1 ){
            $keywords = explode(',',$keyObject->getFieldByPid($lid,'keywords'));
            $infoContent = count($keywords)>5?"【$keywords[0]】等".count($keywords)."个关键词":$keywords ;
        }else{
            $infoContent = $coverMod['file'];
        }
        $excel->getProperties()->setCreator("Phpmarker")->setLastModifiedBy("Phpmarker")->setTitle("Phpmarker")->setSubject("Phpmarker")->setDescription("Phpmarker")->setKeywords("Phpmarker")->setCategory("Phpmarker");
        $excel->setActiveSheetIndex(0)->setTitle('Info');
        //sheet


        //info页
        $objActSheet = $excel->getActiveSheet();
        $objActSheet->getRowDimension(1)->setRowHeight(30);
        $objActSheet->setCellValue('A1', '查询时间')->setCellValue('B1', '方式')->setCellValue('C1', '内容')->setCellValue('D1', '使用IP')->setCellValue('A2', date('Y-m-d H:i:s',$coverMod['time']))->setCellValue('D2', $coverMod['ip'] == 0 ? '本机':'代理')->setCellValue('B2',$coverMod['type'] == 1?'输入':'导入' )->setCellValue('C2',$infoContent );
        $cellStyle = $objActSheet->getStyle('A1:D1');
        $cellStyle->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $cellStyle->getFont()->setSize(12)->setBold(true);
        $cellStyle = $objActSheet->getStyle('A2:D3');
        $cellStyle->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $cellStyle->getFont()->setSize(12);


        $objActSheet->getStyle('A1:D1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(60);
        $objActSheet->getColumnDimension('D')->setWidth(30);
        //sheet
        //详情页
        $kwd = M('keylist');
        $arr = []; $i = 1;
        switch ($type){
            case 1 :
                $pid = $keyObject->getFieldByPid($coverMod['id'],'id');
                $result = $kwd->field('list',true)->where(array('pid'=>$pid))->order('lycos,id')->select();
                $relation = C('PB_RELATION');
                foreach($result as $k=>$v){
                    $arr[$relation[$v['lycos']]][] = $v;
                    unset($arr['lycos']);
                }
                break;
            case 2 :
                $pid = $keyObject->where(['pid'=>$coverMod['id']])->field('id,conditions')->select();
                $relation = C('PB_RELATION');
                foreach($pid as $v){
                    $res = $kwd->field('list',true)->where(array('pid'=>$v['id']))->order('lycos,id')->select();
                    foreach($res as $k=>&$val){
                        $arr[$relation[$val['lycos']]][$v['conditions']][] = $val;
                    }
                }
                ;break;
            default:break;
        }
        foreach($arr as $k=>$data){
            $excel->createSheet();
            $excel->setActiveSheetIndex($i)->setTitle("".$k)->setCellValue('A1', '序号')->setCellValue('B1', '关键词')->setCellValue('C1', '首页排名个数')->setCellValue('D1', '排名情况')->setCellValue('E1', '查看详情');
            $objActSheet = $excel->getActiveSheet();
            $objActSheet->getRowDimension('1')->setRowHeight(30);
            $cellStyle = $objActSheet->getStyle('A1:E1');
            $cellStyle->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE');
            $cellStyle->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $cellStyle->getFont()->setSize(12)->setBold(true);

            if($type == 1){
                foreach($data as $key=>$val){
                    $num = $key+2;
                    $objActSheet->getRowDimension($num)->setRowHeight(30);
                    $objActSheet->setCellValue('A' . $num, $key+1)->getStyle('A'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objActSheet->setCellValue('B' . $num, $val['word'])->getStyle('B'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objActSheet->setCellValue('C' . $num, $val['ranknum'])->getStyle('C'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objActSheet->setCellValue('D' . $num, $val['rankcondition'])->getStyle('D'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objActSheet->setCellValue('E' . $num, '详情')->getStyle('E'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $objActSheet->getCell('E' . $num)->getHyperlink()->setUrl("http://".$_SERVER['HTTP_HOST'].U('Home/Outside/keywordView',array('id'=>$val['id'])));
                    $objActSheet->getStyle('E'.$num)->applyFromArray($this->link_style_array)->getFont()->setSize(8);
                }
            }else{
                $num = 1;
                foreach($data as $kk=>$vv){
                    $num++;
                    $objActSheet->getRowDimension($num)->setRowHeight(50);
                    $objActSheet->setCellValue('A' . $num, $kk)->mergeCells('A'.$num.':E'.$num)->getStyle('A'.$num.':E'.$num)->getFont()->getColor()->setRGB('FF5722');
                    $objActSheet->mergeCells('A'.$num.':E'.$num)->getStyle('A'.$num.':E'.$num)->getFont()->setSize(14)->setBold(true);
                    $objActSheet->mergeCells('A'.$num.':E'.$num)->getStyle('A'.$num.':E'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    foreach($vv as $key=>$val){
                        ++$num;
                        $objActSheet->getRowDimension($num)->setRowHeight(30);
                        $objActSheet->setCellValue('A' . $num, $key+1)->getStyle('A'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objActSheet->setCellValue('B' . $num, $val['word'])->getStyle('B'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objActSheet->setCellValue('C' . $num, $val['ranknum'])->getStyle('C'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objActSheet->setCellValue('D' . $num, $val['rankcondition'])->getStyle('D'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objActSheet->setCellValue('E' . $num, '详情')->getStyle('E'.$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $objActSheet->getCell('E' . $num)->getHyperlink()->setUrl("http://".$_SERVER['HTTP_HOST'].U('Home/Outside/keywordView',array('id'=>$val['id'])));
                        $objActSheet->getStyle('E'.$num)->applyFromArray($this->link_style_array)->getFont()->setSize(8);
                    }
                }
            }


            $objActSheet->getColumnDimension('A')->setWidth(12);
            $objActSheet->getColumnDimension('B')->setWidth(23.5);
            $objActSheet->getColumnDimension('C')->setWidth(18);
            $objActSheet->getColumnDimension('D')->setWidth(18);
            $objActSheet->getColumnDimension('E')->setWidth(18);

            $i++;
        }
        $excel->setActiveSheetIndex(0);
        $filename = "关键词覆盖查询-".date('Y-m-d H时i分',$coverMod['time']);
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = new \PHPExcel_Writer_Excel5($excel);
        $objWriter->save('php://output');
    }


    public function DeadLink(){
        $pid = I('get.id');
        Vendor("phpExcel.PHPExcel");
        $excel =  new \PHPExcel();
        $dead = M('link');
        $deads = M('linklist');
        $info = $dead->find($pid);
        $list = $deads->order('state')->where(array('pid'=>$pid))->select();
        $excel->getProperties()->setCreator("Phpmarker")->setLastModifiedBy("Phpmarker")->setTitle("Phpmarker")->setSubject("Phpmarker")->setDescription("Phpmarker")->setKeywords("Phpmarker")->setCategory("Phpmarker");
        $excel->setActiveSheetIndex(0)->setTitle('sheet');
        //sheet
        //info页
        $objActSheet = $excel->getActiveSheet();
        $objActSheet->getStyle('A1:F1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('2F4056');
        $objActSheet->getStyle('A1:F1')->getFont()->getColor()->setRGB('ffffff');
        $objActSheet->setCellValue('A1', '类型')->setCellValue('B1', '标题')->setCellValue('C1', '网址')->setCellValue('D1', 'nofollow')->setCellValue('E1', '状态')->setCellValue('F1', '重复');
        foreach($list as $k=>$v){
            $repeat = json_decode($v['info'],true);
            $num = $k+2;
            $objActSheet->setCellValue('A'.$num,$v['type']==1?'站内':'站外')->setCellValue('B'.$num,$v['title'])->setCellValue('C'.$num,$v['url'])->setCellValue('D'.$num,$v['nofollow'] == 1? '有':'无')->setCellValue('E'.$num,$v['state'] == 200?'成功':'失败【'.$v['state'].'】')->setCellValue('F'.$num,$repeat['num']);
            if($v['state'] != 200){
                $objActSheet->getStyle('A'.$num.':F'.$num)->getFont()->getColor()->setRGB('FF5722');
            }
        }
        $objActSheet->getColumnDimension('B')->setWidth(60);
        $objActSheet->getColumnDimension('C')->setWidth(60);
        $filename = "死链检测-".date('Y-m-d H时i分',$info['time'])." ".$info['url'];
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = new \PHPExcel_Writer_Excel5($excel);
        $objWriter->save('php://output');
    }


    public function Upload(){
        $return = ['code'=>200,'data'=>''];
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     0 ;// 设置附件上传大小
        $upload->exts      =     array('xls','xlsx');// 设置附件上传类型
        $upload->rootPath  =     $_SERVER['DOCUMENT_ROOT']."/Public/Uploads/"; // 设置附件上传根目录
        $upload->savePath  =     'excel'; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();
        $info = $info['export'];
        if(!$info) {// 上传错误提示错误信息
            $return['code'] = -2;
            $return['msg'] = $upload->getError();
        }else{
            $file_name =  $upload->rootPath.$info['savepath'].$info['savename'];
            $return['title'] = $info['name'];
            $return['count'] = 0;
            Vendor("phpExcel.PHPExcel");
            Vendor("phpExcel.PHPExcel.IOFactory");
            if( $info['ext'] =='xlsx' )
            {
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            }
            else
            {
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            }
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');
            $excelData = array();
            foreach($objPHPExcel->getWorksheetIterator() as $k=>$sheet)  //循环读取sheet
            {
                $excelData[$k] = ['title'=>$sheet->getTitle(),'data'=>''];
                $highestRow = $sheet->getHighestRow();
                $excelData[$k]['count'] += $highestRow;
                for ($row = 2; $row <= $highestRow; $row++) {
                    $excelData[$k]['data'][$sheet->getCellByColumnAndRow(0, $row)->getValue()][] = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                }
            }

            $return['data'] = $excelData;
        }
        $return['pid'] = M('cover')->add(['time'=>time(),'type'=>'2','file'=>$info['name']]);
        $this->ajaxReturn($return);
    }
}
