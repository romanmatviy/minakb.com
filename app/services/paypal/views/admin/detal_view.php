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
							<th>Квитанція про оплату</th>
							<td>#<?=$payment->id?></td>
						</tr>
						<tr>
							<th>Призначення платежу</th>
							<td><strong><?=$payment->details?></strong></td>
						</tr>
						<tr>
							<th>Сума</th>
							<td><strong><?=$payment->amount .' '.$payment->currency_code?></strong></td>
						</tr>
						<tr>
							<th>Статус</th>
							<td><strong><?=$payment->status?></strong></td>
						</tr>
						<tr>
							<th>Службова інформація</th>
							<td><?=$payment->comment?></td>
						</tr>
						<tr>
							<th>Магазин</th>
							<td><a href="<?=SITE_URL.'admin/'.$payment->cart_alias_name?>"><?=$payment->cart_alias_name?></a></td>
						</tr>
						<tr>
							<th>Накладна #</th>
							<td><?=$payment->cart_id?> <a href="<?=SITE_URL.'admin/'.$payment->cart_alias_name.'/'.$payment->cart_id?>" class="btn btn-info btn-xs">Детальніше</a></td>
						</tr>
						<tr>
							<th>Квитанцію сформовано</th>
							<td><?=date('d.m.Y H:i', $payment->date_add)?></td>
						</tr>
						<?php if($payment->status != 'new') { ?>
							<tr>
								<th>Дата останньої операції</th>
								<td><?=date("d.m.Y H:i", $payment->date_edit)?></td>
							</tr>
						<?php } ?>
						<th>Підпис</th>
						<td><?=$payment->signature?> <span class="label label-<?= ($payment->check) ? 'success">Коректний' : 'danger">Увага! Дані пошкоджені!'?></span></td>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php if($payment->history) { ?>
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Історія оплати</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                    	<?php foreach ($payment->history as $row) { ?>
                    		<tr>
                    			<td>#<?=$row->id?></td>
                    			<td><strong><?=$row->status?></strong></td>
                    			<td><?=date("d.m.Y H:i", $row->date)?></td>
                    			<td><span class="label label-<?= ($row->check) ? 'success">Підпис коректний' : 'danger">Увага! Дані пошкоджені!'?></span></td>
                    		</tr>
                    		<tr>
                    			<td colspan="4"><?=$row->details ?></td>
                    		</tr>
                    	<?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<?php } ?>
</div>