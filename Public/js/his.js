layui.config({
    base: '/Public/layui/lay/modules/' //自定义layui组件的目录
}).extend({ //设定组件别名
    datatable: 'datatable'
});
layui.use(['datatable','layer'],function() {
    var table_box = $('.table-box'), Project = table_box.dataTable({
        "searching": true, //是否允许Datatables开启本地搜索
        "autoWidth": false,
        "pageLength": 18,
        "lengthChange": false, //是否允许用户改变表格每页显示的记录数
        "deferRender": true,
        "info": false, //控制是否显示表格左下角的信息
        "ajax": table_box.attr('data-src'),
        "bStateSave":true,
        "order": [0, 'desc'], //asc升序   desc降序
        "columns": [
            {"data": 'id'},
            {"data": 'time'},
            {"data": 'url'},
            {
                "data": "link", "render": function (data) {
                var html = '<a target="ContentBox" href="'+data.infoLink+'" class="layui-btn layui-btn-mini layui-btn-normal" type="info" title="查看详情"> <i class="layui-icon">&#xe64c;</i></a>';
                html += '<a class="layui-btn layui-btn-mini layui-btn-danger" data-url="'+data.delLink+'" type="del" title="删除记录"> <i class="layui-icon">&#xe640;</i></a>';
                html += '<a class="layui-btn layui-btn-mini" href="'+data.ExportLink+'" type="export" title="导出"> <i class="layui-icon">&#xe601;</i></a>';
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
    table_box.on('click','a[type="del"]',function(){
        var _this = $(this);
        layer.confirm('你确定删除这条历史记录？',function(){
            $.ajax({
                type:'delete',
                dataType:'json',
                url:_this.data('url'),
                success:function(state){
                    if(state['code'] === 200){
                        layer.msg('删除成功');
                        Project.api().ajax.reload(null,false);
                    }else if(state['code'] === -2){
                        layer.msg('删除失败')
                    }else{
                        layer.msg('还有这种操作?');
                    }
                }
            })
        })
    });
    $.fn.dataTable.ext.errMode = function (s, h, m) {
    };
});
