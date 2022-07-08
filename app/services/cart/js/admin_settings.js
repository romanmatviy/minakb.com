$( ".table-responsive table tbody" ).sortable({
    handle: ".sortablehandle",
    update: function( event, ui ) {
        $('#saveing').css("display", "block");
        $.ajax({
            url: ALIAS_ADMIN_URL+"settings_change_position",
            type: 'POST',
            data: {
                id: ui.item.attr('id'),
                position: ui.item.index(),
                json: true
            },
            success: function(res){
                if(res['result'] == false){
                    alert("Помилка! Спробуйте ще раз!");
                }
                $('#saveing').css("display", "none");
            },
            error: function(){
                alert("Помилка! Спробуйте ще раз!");
                $('#saveing').css("display", "none");
            },
            timeout: function(){
                alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
                $('#saveing').css("display", "none");
            }
        });
    }
});

function changeActive(e, id) {
    $('#saveing').css("display", "block");
    var flag = 0;
    if(e.checked)
        flag = 1;
    $.ajax({
        url: ALIAS_ADMIN_URL+"settings_change_active",
        type: 'POST',
        data: {
            active : flag,
            id : id,
            json : true
        },
        success: function(res){
            $('#saveing').css("display", "none");
            if(res['result'] == false)
                alert('Помилка! Спробуйте щераз');
            else
            {
                if(flag)
                    $('tr#'+id).removeClass('danger');
                else
                    $('tr#'+id).addClass('danger');
            }
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
            $('#saveing').css("display", "none");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
            $('#saveing').css("display", "none");
        }
    });
}