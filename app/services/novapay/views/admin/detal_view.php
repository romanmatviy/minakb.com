<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Квитанція про оплату #<?=$payment->id?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered nowrap" width="100%">
						<tr>
							<th>Призначення платежу</th>
							<td><strong><?=$payment->details?></strong></td>
						</tr>
						<tr>
							<th>Сума</th>
							<td><strong><?=$payment->amount?></strong></td>
						</tr>
						<tr>
							<th>Статус</th>
							<td><strong><?=$payment->status?></strong> 
								<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/get_status'?>" class="pull-right" method="POST">
									<input type="hidden" name="id" value="<?=$payment->id?>">
									<input type="hidden" name="debug" value="0">
									<button class="btn btn-success btn-xs"><i class="fa fa-refresh" aria-hidden="true"></i> Оновити</button>
								</form>
							</td>
						</tr>
						<tr>
							<th>Службова інформація / NovaPay session id</th>
							<td><?=$payment->novapay_id?></td>
						</tr>
						<tr>
							<th>Накладна #</th>
							<td><a href="<?=SITE_URL.'admin/'.$payment->cart_alias_name.'/'.$payment->cart_id?>" class="btn btn-info btn-xs">Замовлення <?=$payment->cart_id?></a></td>
						</tr>
						<tr>
							<th>Дата останньої операції</th>
							<td><?=date("d.m.Y H:i", $payment->date_edit)?></td>
						</tr>
						<tr>
							<th>Квитанцію сформовано</th>
							<td><?=date('d.m.Y H:i', $payment->date_add)?></td>
						</tr>
						<?php if($payment->status != 'created') { ?>
							<th>Підпис</th>
							<td><?=$payment->signature?> <span class="label label-<?= ($payment->check) ? 'success">Коректний' : 'danger">Увага! Дані пошкоджені!'?></span></td>
						<?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php if($payment->status == 'holded' && $payment->check) { ?>
    	<div class="col-md-6">
	        <div class="panel panel-success">
	            <div class="panel-heading">
	                <h4 class="panel-title">Підтвердити платіж</h4>
	            </div>
	            <div class="panel-body">
	                <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/complete_hold'?>" method="post">
	                	<input type="hidden" name="id" value="<?=$payment->id?>">
	                	<div class="form-group">
	                        <label class="col-md-3 control-label">Зарезервована сума</label>
	                        <div class="col-md-9">
	                            <div class="input-group">
						            <input type="number" name="amount-disabled" value="<?=$payment->amount?>" min="0" step="0.01" disabled required class="form-control">
						            <span class="input-group-addon">грн</span>
						        </div>
	                        </div>
	                    </div>
	                	<button class="btn btn-success btn-sl"><i class="fa fa-check" aria-hidden="true"></i> Підтвердити платіж</button>
	                </form>
	            </div>
	        </div>
	        <div class="panel panel-danger">
	            <div class="panel-heading">
	                <h4 class="panel-title">Скасувати платіж</h4>
	            </div>
	            <div class="panel-body">
	                <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/void'?>" method="post" onsubmit="return confirm('Скасувати платіж?')">
	                	<input type="hidden" name="id" value="<?=$payment->id?>">
	                	<h4>Гроші повернуться клієнту на банківську карту. Замовлення буде переведено в статус "скасовано"</h4>
	                	<button class="btn btn-danger btn-sl"><i class="fa fa-ban" aria-hidden="true"></i> Скасувати платіж</button>
	                </form>
	            </div>
	        </div>
	    </div>
    <?php } ?>
</div>