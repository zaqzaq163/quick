layui.config({
    base: '/Public/layui/lay/modules/' //自定义layui组件的目录
}).extend({ //设定组件别名
    datatable: 'datatable'
});
layui.use(['datatable','layer'],function() {
    var table_box = $('.table-box'), Project = table_box.dataTable({
        "searching": true, //是否允许Datatables开启本地搜索
        "autoWidth": false,
        "pageLength": 15,
        "lengthChange": false, //是否允许用户改变表格每页显示的记录数
        "deferRender": true,
        "info": false, //控制是否显示表格左下角的信息
        "ajax": table_box.attr('data-sourcesrc'),
        "order": [0, 'desc'], //asc升序   desc降序
        "columns": [
            {"data": 'id'},
            {"data": 'ip'},
            {"data": 'is_use',"render":function(data){
                html = data == 0 ? '<span class="tips success"><i class="layui-icon">&#xe605;</i>  </span>' : '<span class="tips"><i class="layui-icon">&#x1006;</i>   </span>';
                return html;
            }},
            {
                "data": "id", "render": function (data) {
                var html = '<a class="layui-btn layui-btn-mini layui-btn-normal" data-id="'+data+'" type="use" title="标为不可用"> <i class="layui-icon">&#x1006;</i></a>';
                html += '<a class="layui-btn layui-btn-mini" data-id="'+data+'" type="unUse" title="标为可用"> <i class="layui-icon">&#xe605;</i></a>';
                html += '<a class="layui-btn layui-btn-mini layui-btn-danger" data-id="'+data+'" type="del" title="删除"> <i class="layui-icon">&#xe640;</i></a>';
                return html;
            }, orderable: false
            }
        ]
    }), layer = layui.layer;
    $('#search_input').keyup(function(e){
        if(e.keyCode===13){
            Project.fnFilter($(this).val());
        }
    });
    $("#search").on('click', function () {
        Project.fnFilter($('#search_input').val());
    });

    table_box.on('click','.layui-btn[type]',function(){
        var type = $(this).attr('type');
        var id = $(this).data('id');
        if(type === 'use'){
            ControlIp('POST',id,1)
        }else if(type === 'unUse'){
            ControlIp('POST',id,0)
        }else{
            layer.confirm('真的要删除?',function(){
                ControlIp('DELETE',id,0)
            })
        }
    });

    function ControlIp(type,id,x){
        $.ajax({
            type:type,
            url:table_box.attr('data-controlsrc'),
            data:{id:id,type:x},
            dataType:'json',
            success:function(res){
                if(res === 200){
                    layer.msg('操作成功');
                    Project.api().ajax.reload(null,false);
                }else{
                    layer.msg('还有这种操作?')
                }
            }
        })
    }

    var DelTip = null,tip=null,num=0,total=0,success=0;
    $('#getIp').on('click',function(){
        tip = layer.msg('正在获取Ip,请稍候',{
            icon:16,
            shade:.5,
            time: 0
        });
        total=($('#getnum').val()/100);
        num = 0;
        success=0;
        GetIp();
    });
    function GetIp(){
        if(num < total){
            $.post(table_box.attr('data-getsrc'),function(res){
                if(res.code === 200){
                    num++;
                    success += parseInt(res.data)
                }else if(res.code === 521){
                    num++
                }
                setTimeout(function(){
                    GetIp()
                },2000);
            },'json')
        }else{
            layer.close(tip);
            if(success === 0){
                layer.msg('一个都没成啊')
            }else{
                layer.msg('总共获取了'+success+"个代理IP")
            }
        }

    }

    $('#DeleteIp').on('click',function(){
        DelTip = layer.msg('正在检测Ip,请稍候',{
            icon:16,
            shade:.5,
            time: 0
        });
        DeleteIp(0);
    });
    function DeleteIp(step){
        $.post(table_box.attr('data-deletesrc'),{'step':step},function(res){
            if(res.code === 301){
                DeleteIp(res.step)
            }else if(res.code === 200){
                layer.close(DelTip);
                layer.msg('删除完成');
                Project.api().ajax.reload(null,false);
            }else if(res.code === -2){
                layer.msg('还有这种操作？')
            }
        },'json')
    }
    $.fn.dataTable.ext.errMode = function (s, h, m) {
    };
});
