$('input[name="mode"]').change(function() {
	if(this.value == 1)
	{
		bonusForm.generate.disabled = false;
		bonusForm.generateLength.disabled = false;
	}
	else
	{
		bonusForm.code.disabled = true;
		bonusForm.generate.disabled = true;
		bonusForm.generateLength.disabled = true;
	}
});
$('input[name="generate"]').change(function() {
	if(this.checked)
	{
		bonusForm.code.disabled = true;
		bonusForm.generateLength.disabled = false;
	}
	else
	{
		bonusForm.code.disabled = false;
		bonusForm.generateLength.disabled = true;
	}
});
$('input[name="count_do"]').change(function() {
	if(this.value >= 0)
		bonusForm.count_do_numbers.disabled = false;
	else
		bonusForm.count_do_numbers.disabled = true;
});
$('input[name="type_do"]').change(function() {
	if(this.value == 'persent')
	{
		bonusForm.persent.disabled = false;
		bonusForm.fixsum.disabled = true;
	}
	else
	{
		bonusForm.persent.disabled = true;
		bonusForm.fixsum.disabled = false;
	}
});
$('input[name="maxActive"], input[name="minActive"]').change(function() {
	if(this.checked)
		this.nextElementSibling.disabled = false;
	else
		this.nextElementSibling.disabled = true;
});