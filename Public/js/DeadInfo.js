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
        "order": [4, 'asc'], //asc升序   desc降序
        "columns": [
            {"data": 'type','render':function(data){
                return data == 1 ? '<span class="tips success">站内</span>':'站外';
            }},
            {"data": 'title'},
            {"data": 'url'},
            {"data": 'nofollow',"render": function (data) {
                return data == 1 ? '<span class="tips success">有</span>':'无';
            }},
            {"data": 'state','render':function(data){
                return data==200 ? '<span class="tips success"><i class="layui-icon">&#xe605;</i>  </span>' : '<span class="tips"><i class="layui-icon">&#x1006;【'+data+'】</i>';
            }},
            {"data": 'info','render':function(data){
                if(data.num>0){
                    var html = '<table class="layui-table"><thead><tr><th>标题</th><th>nofollow</th></tr></thead><tbody>';
                    $.each(data.info,function(index,item){
                        html += '<tr><td>'+item.title+'</td><td>'+item.nofollow+'</td></tr>';
                    });
                    html += '</tbody></table>';
                    return  "<span style='display:none'>"+data.num+"</span><span class='tips link repeatBox' style='cursor: pointer' data-info='" + html + "'>" + data.num + "次</span>"
                }else if(data.num == 0){
                    return '<span style="display:none">'+data.num+'</span><span class="tips">'+data.num+'</span>';
                }
            },"orderData":6},
            {'data':'info.num', "visible": false }
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
    table_box.on('click','.repeatBox',function(){
        var _this = $(this);
        layer.tips(_this.data('info'),_this.parents('td'),{
            tips: [4, '#2F4056'],
            area: ['auto','auto'],
            time: 4000
        });
    });
    $.fn.dataTable.ext.errMode = function (s, h, m) {
    };
});
