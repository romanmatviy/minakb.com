$( "#data-table tbody" ).sortable({
      handle: ".sortablehandle",
      update: function( event, ui ) {
            $('#saveing').css("display", "block");
            $.ajax({
                url: ALIAS_ADMIN_URL+"change_position",
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
$( "#data-table tbody.files" ).disableSelection();

function changeAvailability(e, id) {
    $('#saveing').css("display", "block");
    $.ajax({
        url: ALIAS_ADMIN_URL+"changeAvailability",
        type: 'POST',
        data: {
            availability :  e.value,
            id :  id,
            json : true
        },
        success: function(res){
            $('#saveing').css("display", "none");
            if(res['result'] == false)
                alert('Помилка! Спробуйте щераз');
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

function changeActive(e, id, group) {
    $('#saveing').css("display", "block");
    var flag = 0;
    if(e.checked)
        flag = 1;
    $.ajax({
        url: ALIAS_ADMIN_URL+"changeActive",
        type: 'POST',
        data: {
            active : flag,
            id : id,
            group : group,
            json : true
        },
        success: function(res){
            $('#saveing').css("display", "none");
            if(res['result'] == false)
                alert('Помилка! Спробуйте щераз');
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