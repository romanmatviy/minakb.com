<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-line-chart"></i> До валют</a>
                </div>
                <h4 class="panel-title">Історія</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Внутрішній код</th>
                                <th>Код валюти</th>
								<th>День</th>
								<th>Курс</th>
								<th>Джерело</th>
								<th>Оновлено</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	$this->db->select($_SESSION['service']->table.'_history as h', '*');
                        	$this->db->join($_SESSION['service']->table, 'code', '#h.currency');
                        	$history = $this->db->get('array');
                        	if(!empty($history)) { 
                        		foreach($history as $currency) { ?>
									<tr>
										<td><?=$currency->id?></td>
										<td><?=$currency->code?></td>
										<td><?=date("d.m.Y", $currency->day)?></td>
										<td><b><?=$currency->value?></b></td>
										<td><?=$currency->from?></td>
										<td><?=date("d.m.Y H:i", $currency->update)?></td>
									</tr>
							<?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/jquery.dataTables.js';  
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colReorder.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colVis.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.responsive.js'; 
  $_SESSION['alias']->js_load[] = 'js/admin/table-list.js';
  $_SESSION['alias']->js_init[] = 'TableManageCombine.init();'; 
?>
<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />