<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати перевізника</a>
            	</div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Перевізник</th>
								<th>Сайт</th>
                                <th>Інформація</th>
								<th>Відправка</th>
								<th>Статус</th>
								<th>Додано</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($delivery)) { 
                        		foreach($delivery as $method) { ?>
									<tr>
										<td><?=$method->id?></td>
										<td>
											<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$method->id?>" class='btn btn-sm btn-warning'><i class='fa fa-pencil'></i></a> 
											<b><?=$method->name?></b> 
										</td>
										<td><?=$method->site?></td>
                                        <td><?=$method->info?></td>
										<td><?php switch ($method->department) {
                                            case 1:
                                                echo('у відділення');
                                                break;
                                            case 2:
                                                echo('без адреси');
                                                break;
                                            default:
                                                echo('за адресою');
                                                break;
                                        } ?></td>
										<td><?=$method->active == 1 ? 'активна' : 'відключено'?></td>
										<td><?=date("d.m.Y H:i", $method->date_add)?></td>
									</tr>
							<?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>