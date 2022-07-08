$(function () { 
	var to = false;
	$('#search').keyup(function () {
		if(to) { clearTimeout(to); }
		to = setTimeout(function () {
			var v = $('#search').val();
			$('#jstree').jstree(true).search(v);
		}, 250);
	});

	$('#jstree')
		.on("changed.jstree", function (e, data) {
		    $('#selected').val(data.selected);
		})
		.jstree(
			{plugins: ["wholerow", "checkbox", "search"]}
		);
});