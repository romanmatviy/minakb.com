function setType (e) {
	if(e.value == 'resize')
	{
		$('#div-type-preview').slideUp();
		$('#div-type-resize').slideDown();
		$('#type-preview').attr('name', 'none');
		$('#type-resize').attr('name', 'type');
	}
	if(e.value == 'preview')
	{
		$('#div-type-resize').slideUp();
		$('#div-type-preview').slideDown();
		$('#type-resize').attr('name', 'none');
		$('#type-preview').attr('name', 'type');
	}
}