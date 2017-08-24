layui.use(['form'],function(f){
    var $ = layui.$,b = $('#regSubmit');
    f.on('submit(Login)',function(d){
        $.post(_c,{'u':d.field.UserName,'p':d.field.PassWord},function(r){
            b.val(r.msg);
            if(r.code === 0){
                b.css({'background-color':'#5FB878','color':'#fff'});
                $.post(_l,{'i':r.data},function(e){
                    self.location.href = '/';
                })
            }else{
                b.css({'background-color':'#FF5722','color':'#fff'});
            }
        },'json');
        return false;
    });

    b.blur(function(){
        $(this).val('登录');
        $(this).css({'background-color':'#fff','color':'#5e7cba'})
    })
})