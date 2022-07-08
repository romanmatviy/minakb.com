var errorAmount = 0;

$(".submitBtn").on("click",function (){
	errorAmount = 0;
	var inputs = $(this).closest('.form').find('.important');
	inputs.each(function(){
		var element = $(this);
		var val = $.trim(element.val());
		if (!val){
			element.css({backgroundColor:'#fdd', border:'1px solid #f00'});
			errorAmount++;
		}
	});

	if (errorAmount > 0)
	{
		return false;
	} else {
		var myFuncName = $(this).data('function');
		if(myFuncName) window[myFuncName]();
		var formName = $(this).data('form');
		if(formName) $('#'+formName).modal('close');
	}
	return true;
});

$(".important").on("change",function(){
	if ($.trim($(this).val())=="")
		$(this).css({backgroundColor:'#fdd', border:'1px solid #f00'});
	else
		$(this).css({backgroundColor:'#fff', border:'1px solid #aaa'});
});