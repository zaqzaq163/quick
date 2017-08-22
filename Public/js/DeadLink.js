layui.use(['laytpl','form','table','layer'],function() {
    var form = layui.form,table = layui.table , layer = layui.layer,tableBox = $('.table-box'),cols = [[
        {field: 'type', title: '类型', width: 200 , templet:"<div>{{d.type==1?'<span class='tips success'>站内</span>':'<span class='tips'>站外</span>'}}</div>"}
        ,{field: 'title', title: '标题', width: 300}
        ,{field: 'url', title: '网址',width:300}
        ,{field: 'nofollow', title: 'nofollow', width: 120 , templet:"<div>{{d.nofollow == 1?'<span class='tips success'>有</span>':'<span class='tips'>无</span>'}}</div>"}
        ,{field: 'state',title:'状态', width:150,sort:true , templet:"<div>{{d.state == 200?'<span class='tips success'><i class='layui-icon'>&#xe605;</i></span>':'<span class='tips'><i class=\"layui-icon\">&#x1006;</i></span>'}}</div>"}
        ,{field: 'info',title:'重复', width:150 ,event:'repeat', templet:"<div>{{ d.info.num == 0 ? '<span class='tips'>0</span>' : '<span class='tips link'>'+d.info.num+'</span>' }}</div>"}
    ]];

    form.on('submit(Dead)', function (data) {
        var DeadLink = layer.msg('正在检测', {
            time: 0,
            icon: 16,
            shade: 0.5
        });
        $.post($('#DeadForm').attr('action'), data.field, function (res) {
            tableBox.find('#Export').attr('href', res.url);
            table.render({
                id: tableBox.data('type') || 'quick',
                elem: '.table-box',
                method: 'get',
                url: tableBox.attr('data-src'),
                cols: cols,
                where:{id:res.data},
                even: true,
                page: true,
                limit: 13,
                loading: false,
                done:function(){
                    layer.close(DeadLink);
                }
            });
        });
        return false;
    });


    table.on('tool(info)',function(obj){
        var data = obj.data; //获得当前行数据
        var layEvent = obj.event; //获得 lay-event 对应的值
        if(layEvent === 'repeat'){
            if(data.info.num === 0){
                return false;
            }else{
                var html = '<table class="layui-table"><thead><tr><th>标题</th><th>nofollow</th></tr></thead><tbody>';
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


});


          /*  Project.fnClearTable();
            if(res.data) {
                $.each(res.data, function (index, item) {
                    if (item.repeat.num !== 0) {
                        var html = '';
                        $.each(item.repeat.info, function (i, v) {
                            var nofollow = v.nofollow === 1 ? "有" : "无";
                            html += "标题：" + v.title + "&nbsp;|&nbsp;nofollow：" + nofollow + "<br/>";
                        })
                    }
                    Project.api().row.add([
                        item.type === 1 ? '<span class="tips success">站内</span>' : '站外',
                        item.title,
                        item.url,
                        item.nofollow === 1 ? '<span class="tips success">有</span>' : '无',
                        item.state === 200 ? '<span class="tips success"><i class="layui-icon">&#xe605;</i>  </span>' : '<span class="tips"><i class="layui-icon">&#x1006;【'+item.state+'】</i>   </span>',
                        item.repeat.num === 0 ? '无' : "<span class='tips link repeatBox' style='cursor: pointer' data-info='" + html + "'>" + item.repeat.num + "次</span>"]).draw();
                    table_box.show();
                    $('.dataTables_paginate').show();
                    layer.close(DeadLink);
                });
            }else{
                layer.close(DeadLink);
                table_box.hide();
                $('.dataTables_paginate').hide();
                layer.msg(res.msg,{
                    icon:5,
                    time:5000
                });
            }
        },'json');
        return false
    });





    table_box.on('click','.repeatBox',function(){
        var _this = $(this);
        layer.tips(_this.data('info'),_this.parents('td'));
    });
    $('#search_input').keyup(function(e){
        if(e.keyCode===13){
            Project.fnFilter($(this).val());
        }
    });

    $('#Export').on('click',function(){
        var _this = $(this);
        var getIp = layer.msg('正在导出，请稍候',{
            time:0,
            icon: 16,
            shade:0.5
        });
    });

    $.fn.dataTable.ext.errMode = function (s, h, m) {
    };*/

