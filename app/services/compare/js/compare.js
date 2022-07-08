$(document).ready(function() {
	$.ajax({
        type: "POST",
        url: SERVER_URL+'compare/getItems',
        success: function(res)
        {
            if (res.count)
            {
            	setCompareCount(res.count);

            	res.items.forEach(function (item) {
            		if(obj = $('.add_to_compare[data-alias='+item.alias+'][data-content='+item.content+']'))
            		{
            			obj.data('id', item.id)
            				.addClass('inList')
            				.attr({
            					'src': SERVER_URL + 'style/images/comparison-active.svg',
            					'title': 'До порівняння'
            				});
            		}
            	})
            }
        }
    });
});

$('.add_to_compare').on('click touchstart', function () {
    event.preventDefault();
    var obj = this;
    if(this.dataset.id > 0)
        location.href = SITE_URL+'compare';
    else
        $.ajax({
            url: SITE_URL+'compare/add',
            type: 'POST',
            data: {
                alias: this.dataset.alias,
                content: this.dataset.content,
                ajax: true
            },
            success: function(res) {
                if(res.id > 0)
                {
                    obj.classList.add('inList');
                    obj.dataset.id = res.id;
                    obj.src = SERVER_URL + 'style/images/comparison-active.svg';
                    obj.title = 'До порівняння';
                }
                if (res.count)
                    setCompareCount(res.count);
            }
        })
});

$('i.compare_cancel').on('click touchstart', function () {
	var id = this.dataset.id;
	if(id)
		$.ajax({
			url: SITE_URL+'compare/cancel',
			type: 'POST',
			data: {
				'id' : id
			},
			success:function(res){
				if(res.result)
				{
					$('.compare-'+id).hide(47);
					setCompareCount(res.count);
				}
			}
		});
});

function setCompareCount(count) {
	var i = $('<i/>');
	if(count < 100)
		i.text(count);
	else
		i.addClass('fas fa-plus');
	$('header .actions .comparison').addClass('active').empty().append(i);
	var mobMenu = $('#mobMenu nav a[href$="compare"]');
	if(span = mobMenu.find('span'))
		span.text(count);
	else
		mobMenu.append('<span>'+count+'</span>');
}