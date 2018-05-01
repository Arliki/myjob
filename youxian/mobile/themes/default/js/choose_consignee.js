$('#J_ItemList').on('click','.consignee_info',function(event){
    $(this).prev().find('input').prop('checked',true).trigger('change');
    event.stopPropagation();
})
function choose_address(url,address_id,return_url){
    if(!return_url){
        return_url = document.referrer;
    }
    console.log(return_url);
    $.post(url,{address_id:address_id},function(res){
        if(res.success){
            $.walert(res.info, 1);
            setTimeout(function () {
                self.location=return_url;
            },1800)
        }
    },'json')
}