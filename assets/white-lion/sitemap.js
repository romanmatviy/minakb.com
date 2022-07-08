var multieditPoints = [];
var action = 'save';

$(document).ready(function () {
        //when a submit button is clicked, put its name into the action hidden field
        $(":submit").click(function () { action = this.value; });
    });

function setEditPoint(id)
{
	var index = multieditPoints.indexOf(id);
	if(index >= 0)
		multieditPoints.splice(index, 1);
	else
		multieditPoints.push(id);
}

function multi_edit()
{
	if(multieditPoints.length > 0)
	{
		var ids = '';
		multieditPoints.forEach(function(item, index) {ids=ids+item+',';});
		$('#sitemap-ids').val(ids);
		if(action == 'delete' || action == 'clearCache')
			return confirm("Are you sure you want to delete the selected item?");
		else
			return true;
	}
	else
	{
		$('#modal-notset').modal('show');
	}
	return false;
}

function selectAll(e)
{
	if(e.checked)
	{
		if ($("input.sitemap-multiedit").length !== 0) {
			$("input.sitemap-multiedit").each(function() {
    			var id = $(this).attr("id");
    			$(this).attr("checked", "checked");
    			multieditPoints.push(id);
    		});
    	}
	}
	else
	{
		multieditPoints = [];
		$("input.sitemap-multiedit").attr("checked", false);
	}
}

function setActive(e, field) {
	if(e.checked)
	{
		$('#field-'+field).attr('disabled', false);
		if(field == 'code')
		{
			if($('#field-code').val() == 404)
			{
				switchers["active-index"].disable();
				switchers["field-index"].disable();
				switchers["active-changefreq"].disable();
				switchers["active-priority"].disable();
				$('.index').attr('disabled', 'disabled');
			}
			else
			{
				switchers["active-index"].enable();
				switchers["active-changefreq"].enable();
				switchers["active-priority"].enable();
			}
		}
		if(field == 'index')
		{
			switchers["field-index"].enable();
			if($('#field-index').is(":checked") == false)
			{
				$('.index').attr('disabled', 'disabled');
				if($('#active-changefreq').is(":checked")){
					$('#active-changefreq').click();
				}
				if($('#active-priority').is(":checked")){
					$('#active-priority').click();
				}
				switchers["active-changefreq"].disable();
				switchers["active-priority"].disable();
			}
		}
	}
	else
	{
		$('#field-'+field).attr('disabled', 'disabled');
		if(field == 'index')
		{
			switchers["field-index"].disable();
			switchers["active-changefreq"].enable();
			switchers["active-priority"].enable();
		}
	}
}

function setCode()
{
	if($('#field-code').val() == 404)
	{
		$('.index').attr('disabled', 'disabled');
		if($('#active-changefreq').is(":checked")){
			$('#active-changefreq').click();
		}
		if($('#active-priority').is(":checked")){
			$('#active-priority').click();
		}
		switchers["active-index"].disable();
		switchers["field-index"].disable();
		switchers["active-changefreq"].disable();
		switchers["active-priority"].disable();
	}
	else
	{
		switchers["active-index"].enable();
		switchers["active-changefreq"].enable();
		switchers["active-priority"].enable();
	}
}

function setIndex()
{
	if($('#field-index').is(":checked"))
	{
		switchers["active-changefreq"].enable();
		switchers["active-priority"].enable();
	}
	else
	{
		$('.index').attr('disabled', 'disabled');
		if($('#active-changefreq').is(":checked")){
			$('#active-changefreq').click();
		}
		if($('#active-priority').is(":checked")){
			$('#active-priority').click();
		}
		switchers["active-changefreq"].disable();
		switchers["active-priority"].disable();
	}
}