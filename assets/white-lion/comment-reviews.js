$('.show_feedbacks').click(function(){
   $('.op:hidden').show('slow');
   if($('#feedbackBlock').is(':visible') == false)
   		$('#feedbackBlock').toggle();
   if (this.textContent == "Показать все отзывы")
   		this.textContent = "Скрыть все отзывы";
		else {
		 	this.textContent = "Показать все отзывы";
		 	$('#feedbackBlock').toggle();
		 }
});

function reply (id) {
	$("#replyBlock_"+id).toggle();
}