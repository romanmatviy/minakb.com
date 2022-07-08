<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/wl_aliases/all" class="btn btn-info btn-xs"><i class="fa fa-edit"></i> Загальні налаштування</a>
                </div>
                <h4 class="panel-title">Наявні адреси:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>id</th>
								<th>Головна адреса</th>
								<th>Сервіс</th>
								<th>Таблиця адреси</th>
								<th>Позиція (вага)</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr>
								<td colspan="5"><center><a href="<?=SITE_URL?>admin/wl_aliases/all" class="btn btn-info btn-xs"><i class="fa fa-edit"></i> Загальні налаштування</a></center></td>
							</tr>
                        <?php
                        $this->db->select('wl_aliases');
                		$this->db->join('wl_services', 'name as service_name, title as service_title', '#service');
                		$this->db->order('id');
                		$wl_aliases = $this->db->get('array');
                        if($wl_aliases) foreach ($wl_aliases as $alias) { ?>
							<tr>
								<td><?=$alias->id?></td>
								<td>
									<a href="<?=SITE_URL?>admin/wl_aliases/<?=$alias->alias?>"><?=($alias->admin_ico) ? '<i class="fa '.$alias->admin_ico.'"></i>' : ''?> <?=$alias->alias?></a> 
									<a href="<?=SITE_URL?><?=($alias->alias == 'main') ? '' : $alias->alias?>"><i class="fa fa-eye"></i></a>
								</td>
								<td><a href="<?=SITE_URL?>wl_services/<?=$alias->service_name?>"><?=$alias->service_title?></a></td>
								<td><?=$alias->table?></td>
								<td><?=$alias->admin_order?></td>
							</tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>

                <br>
				<form action="<?=SITE_URL?>admin/wl_aliases/add" method="GET">
					<span title="Адреса повинною бути унікальною!">Додати головну адресу*:</span>
					<input type="text" name="alias" placeholder="адреса" required>
					<?php 
						if($wl_services = $this->db->getAllData('wl_services'))
						{
							echo "<select name='service' required>";
							echo "<option value='0'>відсутній</option>";
							foreach ($wl_services as $s) {
								$go = true;
								if($s->multi_alias == 0)
								{
									if($wl_aliases)
										foreach ($wl_aliases as $alias) {
											if($alias->service == $s->id)
											{
												$go = false;
												break;
											}
										}
								}
								if($go)
									echo "<option value='{$s->id}'>{$s->title}</option>";
							}
							echo "</select>";
						}
						else
							echo "<input type='hidden' id='service' value='0'>";
					?>
					<button>Додати</button>
				</form>
            </div>
        </div>
    </div>
    <!-- end col-12 -->
</div>
<!-- end row -->


