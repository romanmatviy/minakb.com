<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх питань</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
            	</div>
                <h4 class="panel-title">Заповніть дані:</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_group" method="POST">
					<input type="hidden" name="id" value="0">
	                <div class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
							<?php if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
								<tr>
									<th>Назва <?=$lang?></th>
									<td><input type="text" name="name_<?=$lang?>" value="" class="form-control" required></td>
								</tr>
							<?php } else { ?>
								<tr>
									<th>Назва</th>
									<th><input type="text" name="name" value="" class="form-control" required></th>
								</tr>
							<?php } ?>
							<tr>
								<td></td>
								<td><input type="submit" class="btn btn-sm btn-success" value="Додати"></td>
							</tr>
	                    </table>
	                </div>
	            </form>
            </div>
        </div>
    </div>
</div>