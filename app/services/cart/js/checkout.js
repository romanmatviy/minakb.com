$(document).ready(function (){
    $('input[name=phone], input[name=recipientPhone]').mask('+38 (000) 000-00-00');
    if (typeof Sticky === "function")
    {
        var sticky_percents = new Sticky('#cart-checkout #percents');
        var sticky_percents_info = new Sticky('#cart-checkout #percents+.info');
    }
    
    setPercents();

    if (typeof initShipping === "function")
        initShipping();

    if (typeof cities !== "undefined")
    {
        if (typeof autocomplete === "function")
            $("#shipping-cities input").autocomplete({ source: cities }).attr('autocomplete', 'none');
        else
            cities.forEach(function(city) {
                $("#shipping-cities-list").append('<option>'+city+'</option>');
            })
    }
})

$('#cart-signup div').click(function(){
    $(this).parent().find('div').removeClass('active');
    $(this).addClass('active');
    var tab = $(this).data('tab');
    if(tab == 'new-buyer')
    {
        $('#new-buyer').removeClass('hide');
        $('#regular-buyer').addClass('hide');
    }
    else
    {
        $('#new-buyer').addClass('hide');
        $('#regular-buyer').removeClass('hide');
    }
});

$('form input[name=payment_method]').change(function(){
    $('#payments label').removeClass('active');
    $(this).closest('label').addClass('active');
    $('#payments .payment-info').slideUp();
    $('#payment-'+$(this).val()).slideDown();
})

$( '#cart-checkout #oferta' ).change(function() {
    if($( this ).find('input').is(":checked"))
        $( this ).parent().find('i').attr('class', 'fas fa-check-square');
    else
        $( this ).parent().find('i').attr('class', 'far fa-square');
})

$( 'form#confirm' ).find( 'select, textarea, input' ).change(function() { setPercents() })
function setPercents() {
    var all_elements = 0, empty_elements = 0, percents = 100;
    radio_names = [];
    $( 'form#confirm' ).find( 'select, textarea, input' ).each(function(){
        if( $( this ).prop( 'required' )){
            var type = $( this ).prop( 'type' ),
                name = $( this ).prop( 'name' );
            if(type == 'radio') {
                if(! radio_names.includes(name))
                {
                    radio_names.push(name);
                    all_elements++;
                    if(! $( 'form#confirm' ).find('input:radio[name="'+name+'"]').is(":checked"))
                        empty_elements++;
                }
            } else
                all_elements++;

            if(type == 'checkbox') {
                if(! $( this ).is(":checked"))
                    empty_elements++;
            } else if ( ! $( this ).val() )
                empty_elements++;
        }
    });
    if(all_elements > 0)
        percents = Math.ceil((all_elements - empty_elements) / all_elements * 100);
    $('table.__cart_products_list .name_action ~ table tbody tr td.amount input').each(function() {
        var val = $( this ).val(),
            value = isInt(val) ? parseInt(val) : 0;
        if(value <= 0 && percents > 0)
            percents--;
    })
    $('#percents .active').animate({width: percents+'%'});
    $('#percents .text').text(percents+'%');
    if(percents == 100)
        $('form#confirm button.checkout').attr('disabled', false).addClass('active');
    else
        $('form#confirm button.checkout').attr('disabled', true).removeClass('active');
}

$('#cart-checkout').find('[name="shipping-method"]').change(function () {
	$("#divLoading").addClass('show');

	$("#shipping_to_cart").slideUp().empty();
	$("#shipping-info").hide().empty();
	$("#shipping-country, #shipping-city, #shipping-department, #shipping-address").addClass('hide');
    $("#shipping-country input, #shipping-city input, #shipping-department input, #shipping-address textarea").attr('required', false);

    $('#cart-checkout').find('tr.cart-subtotal, tr.cart-shipping').addClass('hide');

	$.ajax({
        url: SITE_URL + 'cart/get_shipping',
        type: 'POST',
        data: {
            shipping_id: $(this).val(),
            ajax: true
        },
        complete: function() {
            $("div#divLoading").removeClass('show');
            if (typeof initShipping === "function")
                initShipping();
        },
        success: function(order) {
            if(order.shipping.info != '')
            	$("#shipping-info").html(order.shipping.info).slideDown();
            if(order.shipping.html != '')
            	$("#shipping_to_cart").html(order.shipping.html).slideDown();
            order.shipping.type_fields.forEach((field) => {
            	$("#shipping-"+field).removeClass('hide');
            	if(field == 'address')
            		$("#shipping-address textarea").attr('required', true);
            	else
            		$("#shipping-"+field+" input").attr('required', true);
            });

            if(order.shipping.pay_action != 'hide')
            {
            	$('#cart-checkout').find('tr.cart-subtotal, tr.cart-shipping').removeClass('hide');
            	$('#cart-checkout').find('tr.cart-subtotal span.amount').text(order.subTotalFormat);
            	$('#cart-checkout').find('tr.cart-shipping span.amount').text(order.shipping.priceFormat);
            }
            $('#cart-checkout').find('tr.cart-total span.amount').text(order.totalFormat);

            let payments = $('#cart-checkout').find('.checkout-payment');
            if(order.shipping.pay_action == 'by_manager')
            {
            	payments.addClass('hide');
            	$('input[name=payment_method][value=0]').attr('disabled', false).prop('checked', true);
            }
            else if(payments.hasClass('hide'))
            {
            	payments.removeClass('hide');
            	$('input[name=payment_method][value=0]').attr('disabled', true).prop('checked', false);
            }
        }
    });
});


$("#cart input#email").on("change", function() {
    $("#divLoading").addClass('show');
    email = $(this).val();
    $.ajax({
        url: SITE_URL + 'cart/checkUser',
        type: 'POST',
        data: {
            email: email,
            ajax: true
        },
        complete: function() {
            $("div#divLoading").removeClass('show');
        },
        success: function(res) {
            if (res.result == true)
            {
                $('#new-buyer').addClass('hide');
                $('#regular-buyer, #cart_notify').removeClass('hide');
                $('#cart_notify').removeClass('alert-danger').addClass('alert-success');
                $('#cart #regular-buyer input[name=email_phone]').val(res.email);
                $('#cart #cart_notify p').html(res.message);
                $('#cart #regular-buyer input[name=password]').focus();
            }
            else
            {
                $( 'form#confirm' ).find( 'input[name="email"]' ).val(email);
                $('#new-buyer').removeClass('hide');
                $('#regular-buyer').addClass('hide');
                if($('#recipientName').val() == '')
                    $('#recipientName').val($('#cart input#name').val());
                setPercents()
            }
        }
    })
});
$("#cart input#phone").on("change", function() {
    var phone = $(this).val(),
        recipientPhone = $('form#confirm input[name="recipientPhone"]');
    $( 'form#confirm' ).find( 'input[name="phone"]' ).val(phone);
    // if(recipientPhone.val() == '')
        recipientPhone.val(phone);
    setPercents()
});
$("#cart input#name, #cart input#surname").on("change", function() {
    var name = $('#cart input#name').val(),
        surname = $('#cart input#surname').val(),
        recipientName = $('form#confirm input[name="recipientName"]'),
        recipientSurName = $('form#confirm input[name="recipientSurName"]');
    recipientName.val(name);
    recipientSurName.val(surname);
    $( 'form#confirm' ).find( 'input[name="name"]' ).val(name + ' ' + surname);
        
    setPercents()
});

function facebookSignUp() {
    FB.login(function(response) {
        if (response.authResponse) {
            $("#divLoading").addClass('show');
            var accessToken = response.authResponse.accessToken;
            FB.api('/me?fields=email', function(response) {
                if (response.email && accessToken) {
                    $('#authAlert').addClass('collapse');
                    $.ajax({
                        url: SITE_URL + 'login/facebook',
                        type: 'POST',
                        data: {
                            accessToken: accessToken,
                            ajax: true
                        },
                        complete: function() {
                            $("div#divLoading").removeClass('show');
                        },
                        success: function(res) {
                            if (res['result'] == true) {
                                location.reload();
                            } else {
                                $('#authAlert').removeClass('collapse');
                                $("#authAlertText").text(res['message']);
                            }
                        }
                    })
                } else {
                    $("div#divLoading").removeClass('show');
                    $("#clientError").text('Для авторизації потрібен e-mail');
                    setTimeout(function(){$("#clientError").text('')}, 5000);
                    FB.api("/me/permissions", "DELETE");
                }
            });
        } else {
            $("div#divLoading").removeClass('show');
        }

    }, { scope: 'email' });
    return false;
}