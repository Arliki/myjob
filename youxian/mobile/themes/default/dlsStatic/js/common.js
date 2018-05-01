;$.extend({
    walert: function (info, time) {
        if(!time){
            time = 2;
        }
        if($('.walert-div').length==0){
            var _ele = "<div class='walert-div' style='width:80%;margin:0 auto;background:#000;opacity:.8;text-align:center;padding:.8rem;font-size:1rem;border-radius:.3rem;color:#fff;position: fixed;top:50%;left: 50%;margin-left:-40%;'>"+info+"</div>";
            $('body').append(_ele);
        }else{
            $('.walert-div').text(info);
        }
        $('.walert-div').fadeIn(1500);
        setTimeout(function(){
            $('.walert-div').fadeOut(1500);
        },time*1000)
    }
});

