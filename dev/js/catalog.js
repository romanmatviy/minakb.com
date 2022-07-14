$('form .filter label').click(function () {
	$('form .filter .options label button').removeClass('show');
    $(this).find('button').addClass('show');
    if($(this).find('input').is(':checked'))
    {
    	$(this).addClass('active');
        $(this).find('i.fa-square').addClass('fa-check-square').removeClass('fa-square');
    }
    else
    {
        $(this).removeClass('active');
        $(this).find('i.fa-check-square').removeClass('fa-check-square').addClass('fa-square');
    }
})
$('form .filter h6, form .filter i.angle').click(function () {
	var i = $(this).parent().find('i.angle');
    if(i.hasClass('fa-angle-down'))
    {
    	i.removeClass('fa-angle-down').addClass('fa-angle-up');
    	$(this).parent().find('.options').slideUp();
    }
    else
    {
    	i.addClass('fa-angle-down').removeClass('fa-angle-up');
    	$(this).parent().find('.options').slideDown();
    }
})
$('form button[type="reset"]').click(function () {
    event.preventDefault();
    $('form .filter .options label button').removeClass('show');
	$(this).closest('form').find('label').removeClass('active');
	$(this).closest('form').find('label i.fa-check-square').removeClass('fa-check-square').addClass('fa-square');
	$(this).closest('form').find('input').attr('checked', false);
    $(this).closest('form').find('input[type=search]').val('');
	// $(this).closest('form').find('.options').addClass('hide');
	$(this).closest('form').find('.filter > i').removeClass('fa-angle-down').addClass('fa-angle-up');
	$("html, body").animate({ scrollTop: 0 }, "slow");
})
$('form .filter .options .clear + .more').click(function () {
	var i = $(this).find('i.fas');
    if(i.hasClass('fa-angle-down'))
    {
    	i.removeClass('fa-angle-down').addClass('fa-angle-up');
    	$(this).parent().find('.more.hide').slideDown();
    	$(this).find('.close').removeClass('hide');
    	$(this).find('.open').addClass('hide');
    }
    else
    {
    	i.addClass('fa-angle-down').removeClass('fa-angle-up');
    	$(this).parent().find('.more.hide').slideUp();
    	$(this).find('.close').addClass('hide');
    	$(this).find('.open').removeClass('hide');
    }
});

$('.fa-sliders-h').click(function(){
    $('aside').removeClass('m-hide').toggle(540);
});
