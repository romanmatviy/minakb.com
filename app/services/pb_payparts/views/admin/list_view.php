<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<?php if($_SESSION['user']->admin) { ?>
	            	<div class="panel-heading-btn">
	                    <a href="<?= SITE_URL.'admin/wl_aliases/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування</a>
	                </div>
	            <?php } ?>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Список всіх оплат</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Квитанція №</th>
                                <th>Статус</th>
								<th>Оновлено</th>
								<th>Замовлення</th>
								<th>Сума</th>
								<?php if($_SESSION['option']->useMarkUp) { ?>
									<th>Націнка</th>
								<?php } ?>
								<th>Тип кредиту</th>
								<th>Деталі</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($payments)) { 
                        		foreach($payments as $pay) { ?>
									<tr class="<?=$pay->status == 'SUCCESS' ? 'success' : ''?><?=$pay->status == 'FAIL' ? 'danger' : ''?>">
										<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$pay->id?>" class="btn btn-info btn-xs">Квитанція #<?=$pay->id?></a>
										</td>
										<th><?=$pay->status?></th>
										<td><?=date("d.m.Y H:i", $pay->date_edit)?></td>
										<td><a href="<?=SITE_URL.'admin/'.$pay->cart_alias_name.'/'.$pay->cart_id?>" class="btn btn-success btn-xs">Замовлення <?=$pay->cart_id?></a></td>
										<th><?=$pay->amount?> UAH</th>
										<?php if($_SESSION['option']->useMarkUp) { ?>
											<td><?=$pay->markup?></td>
										<?php } ?>
										<td>
											<?php switch ($pay->merchant_type) {
												case 'II':
													echo "II - Миттєва розстрочка";
													break;
												case 'PP':
													echo "PP - Оплата частинами";
													break;
												case 'PB':
													echo "PB - Оплата частинами. Гроші в періоді";
													break;
												case 'IA':
													echo "IA - Миттєва розстрочка. Акція 50/50%";
													break;
											} ?>
										</td>
										<td><?=$this->data->getShortText($pay->comment, 25)?></td>
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