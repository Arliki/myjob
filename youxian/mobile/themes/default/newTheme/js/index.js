$(function() {
    $('.body-model .close').on('click',function(){$('.body-model').fadeOut();});
    $('.body-model .wmask').on('click',function(){$('.body-model').fadeOut();});
    //滚动广告
    var idx = 0;
    setInterval(function(){
        idx++;
        $(".star ul").animate({"top":-45*idx},600,function(){
            if(idx >= $(".star ul li").length){
                idx = 0;
                $(".star ul").css("top",0);
            }
        });
    },2000);
    //轮播插件
    var swiper1 = new Swiper('.banner-inner.swiper-container', {
        pagination: '.swiper-pagination',
        paginationClickable: '.swiper-pagination',
        spaceBetween: 10,
        effect: 'fade',
        autoplay : 4000,
        grabCursor : true,
        loop : true
    });
    $.get($('.body-model').data('url'),'',function(res){
        if(res.showAd){
            var ad = res.ad;
            $('.body-model #model-con-img').attr('src',ad.ad_code);
            $('#model-con-img').on('click',function(){
                if(ad.ad_link){
                    window.location.href = ad.ad_link;
                }
            })
            $('.body-model').fadeIn();
        }
    },'JSON')


    // 请求数据-渲染模板
    var template=$('#template').html();

    $.ajax({
        type:"get",
        url:$('#container').data('url'),
        async:true,
        success: function(data){
            //  // Data = data.goods;
            var Data=JSON.parse(data);
            var addata=Data.cat_goods;
            $('#container').html(doT.template(template )(addata));
            var swiper = new Swiper('.swiper-container2', {
//                    pagination: '.swiper-pagination',
                slidesPerView: 3.5,
                paginationClickable: true,
                spaceBetween: 0
            });
        },
        error: function(XMLHTTPRequest, textStatus, errorThrown){
            console.log("XMLHttpRequest :"+ XMLHTTPRequest);
            console.log("textStatus :"+textStatus);
            console.log("errorThrown :"+errorThrown);
        }
    });
})