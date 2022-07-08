<?php

	$forms = $this->db->getAllData('wl_forms');

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/wl_forms/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати форму</a>
                </div>
                <h4 class="panel-title">Наявні форми:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th width="100px" nowrap>ID</th>
								<th>alias/uri форми</th>
								<th>Назва</th>
								<th>Captcha</th>
								<th>Робоча таблиця</th>
								<th>Метод запиту</th>
                                <th>Тип зберігання</th>
								<th>З відправкою емейлу</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php if($forms) foreach ($forms as $f) { ?>
								<tr>
									<td><a href="<?=SITE_URL?>admin/wl_forms/<?=$f->name?>"><?=$f->id?></a></td>
                                    <td><a href="<?=SITE_URL?>admin/wl_forms/<?=$f->name?>" class="btn btn-xs btn-info"><?=$f->name?></a></td>
									<td><?=$f->title?></td>
                                    <td><?=($f->captcha)?'Так':'Ні'?></td>
									<td><?=$f->table?></td>
									<td><?=($f->type == 1)?'GET':'POST'?></td>
									<td><?=($f->type_data == 1)?'Кожне поле новий рядок':'Структурована таблиця'?></td>
                                    <td><?=($f->send_mail == 1)?'Так':'Ні'?></td>
								</tr>
							<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>