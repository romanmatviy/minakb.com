<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Список всіх оплат</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Квитанція №</th>
								<th>Дата</th>
								<th>Магазин</th>
								<th>Сума</th>
								<?php if($_SESSION['option']->useMarkUp) { ?>
									<th>Націнка</th>
								<?php } ?>
								<th>Статус</th>
								<th>Деталі</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($payments)) { 
                        		foreach($payments as $pay) { ?>
									<tr>
										<td>#<?=$pay->id?> <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$pay->id?>" class="btn btn-info btn-xs">Детальніше</a>
										</td>
										<td><?=date("d.m.Y H:i", $pay->date_edit)?></td>
										<td><?=$pay->cart_alias_name?> <a href="<?=SITE_URL.'admin/'.$pay->cart_alias_name.'/'.$pay->cart_id?>" class="btn btn-info btn-xs">до замовлення</a></td>
										<td><b><?=$pay->amount?></b></td>
										<?php if($_SESSION['option']->useMarkUp) { ?>
											<td><?=$pay->markup?></td>
										<?php } ?>
										<td><?=$pay->status?></td>
										<td><?=$pay->details?></td>
									</tr>
							<?php } } ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $this->load->library('paginator');
                echo $this->paginator->get();
                ?>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
	.search-row {
	    max-width: 800px;
	    margin-left: auto;
	    margin-right: auto;
	}
	.search-row .search-col {
	    padding: 0;
	    position: relative;
	}
	.search-row .search-col:first-child .form-control {
	    border: 1px solid #16A085;
	    border-radius: 3px 0 0 3px;
	    margin-bottom: 20px;
	}
</style>