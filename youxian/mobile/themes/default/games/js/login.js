$(function() {
    var btn = document.getElementById("getcode");
    var phonenum=$("#phonenum").val();
    var get_code_url = $(btn).data('url');
    var verify_code_url = $(btn).data('c-url');
    //调用监听
    monitor($(btn));

    // 手机号输入监听
    $(document).on("blur","#phonenum",function(){
        var phonenum=$("#phonenum").val()
        if((/^1[34578]\d{9}$/.test(phonenum))){
            $(".label1 .tips").hide();
        }else{
            $(".label1 .tips").html("手机号码不正确").show()
        }
    })

    // 获取验证码
    btn.onclick = function() {
        var phonenum=$("#phonenum").val()
        //倒计时效果  getCode回调函数
        var _this = $(this);
        if((/^1[34578]\d{9}$/.test(phonenum))){
            $.get(get_code_url,{phone: phonenum}, function (res) {
                alert(res.info);
                if(res.success){
                    countDown(_this, getCode);
                }
            },'json')

        }else{
            $(".label1 .tips").html("手机号码不正确").show()
        }

    };

    function getCode() {
        // alert("验证码发送成功");
    };

    $(".yesbtn").click(function(){
        var phonenum=$("#phonenum").val();
        var checknum=$("#check").val();
        // 验证手机号
        if((/^1[34578]\d{9}$/.test(phonenum))){
            // 验证验证码
            if(/^\d{6}$/.test(checknum)){
                // 验证码匹配验证
                $.ajax({
                    type: "post",
                    url: verify_code_url,
                    async: true,
                    dataType: 'json',
                    data: {"phone":phonenum,"code":checknum},
                    success: function(res){
                        alert(res.info);
                        if(res.success){
                            window.location.reload();
                        }
                        // if(res.num==2){
                        //     alert("手机号已注册，请换号注册")
                        // }else if(res.num==0){
                        //     alert("验证码不正确")
                        // }else{
                        //     window.location.href="../../../index.html"
                        // }

                    },
                    error: function(){
                        alert("请刷新重试")
                    }
                })
            }else{
                $(".label2 .tips").html("验证码不正确").show()
            }
        }else{
            $(".label1 .tips").html("手机号码不正确").show()
        }
    })
});
function monitor(obj) {
    var LocalDelay = getLocalDelay();
    if(LocalDelay.time!=null){
        var timeLine = parseInt((new Date().getTime() - LocalDelay.time) / 1000);
        if (timeLine > LocalDelay.delay) {

        } else {
            _delay = LocalDelay.delay - timeLine;
            obj.text(_delay+"秒后重新发送");
            document.getElementById("getcode").disabled = true;
            obj.text(_delay+"秒后重新发送").addClass("bggrey");
            var timer = setInterval(function() {
                if (_delay > 1) {
                    _delay--;
                    obj.text(_delay+"秒后重新发送").addClass("bggrey");
                    obj.attr('disabled',true).css('cursor','not-allowed')
                    setLocalDelay(_delay);
                } else {
                    clearInterval(timer);
                    obj.text("获取验证码").removeClass("bggrey");
                    obj.removeClass('disabled').removeAttr('disabled style');
                    document.getElementById("getcode").disabled = false;
                }
            }, 1000);
        }
    }
};


//倒计时效果
/**
 *
 * @param {Object} obj 获取验证码按钮
 * @param {Function} callback  获取验证码接口函数
 */
function countDown(obj, callback) {
    if (obj.text() == "获取验证码") {
        var _delay = 60;
        var delay = _delay;
        obj.text(_delay+"秒后重新发送");
        document.getElementById("getcode").disabled = true;
        obj.text(_delay+"秒后重新发送").addClass("bggrey");
        var timer = setInterval(function() {
            if (delay > 1) {
                delay--;
                obj.html(delay+"秒后重新发送").addClass("bggrey");
                obj.attr('disabled',true).css('cursor','not-allowed');
                setLocalDelay(delay);
            } else {
                clearInterval(timer);
                obj.text("获取验证码").removeClass("bggrey");
                obj.removeClass('disabled').removeAttr('disabled style')
                document.getElementById("getcode").disabled = false;
            }
        }, 1000);

        callback();
    } else {
        return false;
    }
}
//设置setLocalDelay
function setLocalDelay(delay) {
    //location.href作为页面的唯一标识，可能一个项目中会有很多页面需要获取验证码。
    sessionStorage.setItem("delay_" + location.href, delay);
    sessionStorage.setItem("time_" + location.href, new Date().getTime());
}

//getLocalDelay()
function getLocalDelay() {
    var LocalDelay = {};
    LocalDelay.delay = sessionStorage.getItem("delay_" + location.href);
    LocalDelay.time = sessionStorage.getItem("time_" + location.href);
    return LocalDelay;
}