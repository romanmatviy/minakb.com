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

$('#selectAllProducts').change(function(event) {
    if (this.checked) {
        $('#data-table tbody tr').addClass('info');
        $('#data-table tbody td.move input').attr('checked', true);
    }
    else
    {
        $('#data-table tbody tr').removeClass('info');
        $('#data-table tbody td.move input').attr('checked', false);
    }
});
$('#data-table tbody td.move input').change(function(event) {
    if (this.checked) {
        $(this).closest('tr').addClass('info');
    }
    else
    {
        $(this).closest('tr').removeClass('info');
    }
});
function multi_editProducts(field, value) {
    var inputs = $('#data-table tbody td.move input:checked');
    if(inputs.length == 0)
    {
        alert('Спершу оберіть товари');
    }
    else
    {
        var p_ids = '';
        $.each(inputs, function(index, input) {
             p_ids += input.value+',';
        });
        $('form#multi_editProducts input[name="products"]').val(p_ids);
        $('form#multi_editProducts input[name="field"]').val(field);
        $('form#multi_editProducts input[name="value"]').val(value);
        $('form#multi_editProducts').submit();
    }
    return false;
}

$('#deleteProduct').on('show.bs.modal', function (event) {
    var a = $(event.relatedTarget)
    var pid = a.data('pid')
    var name = a.data('name')

    var modal = $(this)
    modal.find('.modal-title strong').text(name)
    modal.find('form input').val(pid)
});
$('#multi-changeGroup').on('show.bs.modal', function (event) {
    var p_ids = '';
    var inputs = $('#data-table tbody td.move input:checked');
    if(inputs.length == 0)
    {
        alert('Спершу оберіть товари');
        return false;
    }

    $.each(inputs, function(index, input) {
         p_ids += input.value+',';
    });

    var modal = $(this)
    modal.find('form input[name="products"]').val(p_ids)
});
$('#multi-deleteProducts').on('show.bs.modal', function (event) {
    var p_ids = '', list = '';
    var inputs = $('#data-table tbody td.move input:checked');
    if(inputs.length == 0)
    {
        alert('Спершу оберіть товари');
        return false;
    }

    $.each(inputs, function(index, input) {
         p_ids += input.value+',';
         list += '<li>'+$(input).closest('tr').find('a.product_name').html()+'</li>';
    });

    var modal = $(this)
    modal.find('form input[name="products"]').val(p_ids)
    modal.find('.modal-body ul').html(list)
})