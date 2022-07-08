<!-- begin row -->
<div class="row row-space-30">

    <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias ?>/test" method="post" class="form-horizontal">
		<div class="form-group">
            <label class="control-label col-md-3">pay_id <span class="text-danger">*</span></label>
            <div class="col-md-9">
                <input type="number" class="form-control" name="pay_id" value="<?=$this->data->re_post('pay_id')?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-3">status <span class="text-danger">*</span></label>
            <div class="col-md-9">
            	<select name="status" class="form-control">
            		<option value="success" <?=($this->data->re_post('status') == 'success') ? 'selected' : 'selected' ?>>success</option>
            		<option value="sandbox" <?=($this->data->re_post('sandbox') == 'sandbox') ? 'selected' : '' ?>>sandbox</option>
            		<option value="processing" <?=($this->data->re_post('status') == 'processing') ? 'selected' : '' ?>>processing</option>
            		<option value="failure" <?=($this->data->re_post('status') == 'failure') ? 'selected' : '' ?>>failure</option>
            	</select>
            </div>
        </div>
        <div class="form-group">
        	<button type="submit" class="btn btn-sm btn-success col-md-offset-4 col-md-2">Test!</button>
        </div>
	</form>
</div>
<!-- end row -->

<?php if(isset($res)) 
{
	echo('<div class="row row-space-30 m-40">');
	var_dump($res);
	echo('</div>');
}
?>