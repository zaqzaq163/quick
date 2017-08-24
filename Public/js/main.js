layui.use(['table','layer'],function() {
    var table = layui.table , layer = layui.layer,tableBox = $('.table-box'),cols = null;
    switch (tableBox.data('where')){
        case 'KwdHis':
            cols = [[ //标题栏
                {checkbox: true}
                ,{field: 'id', title: 'ID', width: 80,sort:true}
                ,{field: 'time', title: '查询时间', width: 200}
                ,{field: 'type', title: '方式', width: 120}
                ,{field: 'content', title: '内容',width:300}
                ,{field: 'ip', title: 'IP', width: 120}
                ,{title:'操作', width:150, align:'center', toolbar: '#Group'}
            ]];
            break;
        case 'DeadInfo':
            cols = [[
                {field: 'type', title: '类型', width: 200 , templet:"<div>{{d.type==1?'<span class='tips success'>站内</span>':'<span class='tips'>站外</span>'}}</div>"}
                ,{field: 'title', title: '标题', width: 300}
                ,{field: 'url', title: '网址',width:300 , templet:"<div><a class='tips link' href='{{ d.url }}' target='_blank'>{{ d.url }}</a></div>"}
                ,{field: 'nofollow', title: 'nofollow', width: 120 , templet:"<div>{{d.nofollow == 1?'<span class='tips success'>有</span>':'<span class='tips'>无</span>'}}</div>"}
                ,{field: 'state',title:'状态', width:150,sort:true , templet:"<div>{{d.state == -1?'<span class='tips success'><i class='layui-icon'>&#xe605;</i></span>':'<span class='tips' title=\"'+d.state+'\"><i class='layui-icon'>&#x1006;</i></span>'}}</div>"}
                ,{field: 'info',title:'重复', width:150 ,event:'repeat', templet:"<div>{{ d.info.num == 0 ? '<span class='tips'>0</span>' : '<span class='tips link'>'+d.info.num+'</span>' }}</div>"}
            ]];
            break;
        case 'DeadHis':
            cols = [[ //标题栏
                {checkbox: true}
                ,{field: 'id', title: 'ID', width: 80,sort:true}
                ,{field: 'time', title: '查询时间', width: 200}
                ,{field: 'url', title: '入口', width: 300}
                ,{title:'操作', width:150, align:'center', toolbar: '#Group'}
            ]];
            break;
    }
    table.render({
        id :tableBox.data('type') || 'quick',
        elem:'.table-box',
        method:'post',
        url :tableBox.data('src'),
        cols:cols,
        even:true,
        page:true,
        limit:17
    });



    table.on('tool(info)',function(obj){
        var data = obj.data; //获得当前行数据
        var layEvent = obj.event; //获得 lay-event 对应的值
        if(layEvent === 'repeat'){
            if(data.info.num === 0){
                return false;
            }else{
                var html = '<table class="layui-table repeatTable" lay-size="sm" lay-skin="line"><thead><tr><th>标题</th><th>nofollow</th></tr></thead><tbody>';
                $.each(data.info.info,function(index,item){
                    html += '<tr><td>'+item.title+'</td><td>'+item.nofollow+'</td></tr>';
                });
                html += '</tbody></table>';

                layer.tips(html,obj.tr.find('td').last(),{
                    tips: [1, '#2F4056'],
                    area: ['auto','auto'],
                    time: 4000
                });
            }
        }
    });
    table.on('tool(quick)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        var data = obj.data; //获得当前行数据
        var layEvent = obj.event; //获得 lay-event 对应的值
        if(layEvent === 'view'){ //查看
            window.open(data.link['infoLink'],'ContentBox');
        } else if(layEvent === 'del'){ //删除
            layer.confirm('真的删除行么', function(index){
                $.ajax({
                    type:'delete',
                    dataType:'json',
                    url:data.link['delLink'],
                    success:function(state){
                        if(state['code'] === 200){
                            layer.msg('删除成功');
                            obj.del(); //删除对应行（tr）的DOM结构
                        }else{
                            layer.msg('删除失败【未知错误】')
                        }
                    }
                });
                layer.close(index);
                //向服务端发送删除指令
            });
        } else if(layEvent === 'export'){
            window.open(data.link['ExportLink'],'_blank');
        }
    });
});
