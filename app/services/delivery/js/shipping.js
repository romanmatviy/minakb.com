function changeInfo(el) {

    active_shipping_method = $(el).val();

    $("#shipping-info").text(information[active_shipping_method]);

    if(departments[active_shipping_method] == '1')
    {
        $("#CityInput").removeClass('hidden');
        if($("#shipping-method option:selected").text().toLowerCase() != 'нова пошта'.toLowerCase()){
            $("#shipping-department, #shipping-address").addClass('hidden').empty();
            $("#shipping-department-other").removeClass('hidden');
        }
        else {
            $("#shipping-department-other, #novaPoshtaDepartments, #shipping-address").addClass('hidden');
            $("#shipping-cities").val('');
        }
    }
    else if(departments[active_shipping_method] == '2')
    {
        $("#CityInput, #shipping-address, #shipping-department, #shipping-department-other, #novaPoshtaDepartments").addClass('hidden');
        $("#shipping-address, #shipping-department").attr('required', '');
    }
    else
    {
        $("#shipping-department, #shipping-department-other, #novaPoshtaDepartments").addClass('hidden');
        $("#CityInput").removeClass('hidden');
        $("#shipping-address, #shipping-department").attr('required', 'required');
    }
}

$("#shipping-cities").autocomplete({
    source: cities,
    select: function (event, ui) {
        if(departments[active_shipping_method] == '1')
        {
            var address = ui.item.value;

            $("#novaPoshtaDepartments").removeClass('hidden');

            if($("#shipping-method option:selected").text().toLowerCase() == 'нова пошта'.toLowerCase()){
                $("#shipping-department-other").addClass('hidden');
                $("#shipping-department").removeClass('hidden').empty().append('<option selected disabled="" value="">Виберіть відділення</option>');

                $.each(warehouse_by_city[ui.item.value], function(i, p) {
                     $("#shipping-department").append($('<option></option>').val('№'+p.number+' : '+p.address).html('№'+p.number+' : '+p.address));
                });
            }
            else {
                $("#shipping-department").addClass('hidden').empty();
                $("#shipping-department-other").removeClass('hidden');
            }
        }
        else
        {
            $("#shipping-department, #shipping-department-other, #novaPoshtaDepartments").addClass('hidden');
            $("#shipping-address").removeClass('hidden');
        }
    }
});