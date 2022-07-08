<?php require_once APP_PATH.'services'.DIRSEP.$_SESSION['service']->name.DIRSEP.'views'.DIRSEP.'admin'.DIRSEP.'__search_subview.php'; 

$wl_user_types = false;
// $wl_user_types = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(!empty($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					<?php } ?>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name .'. Пошук '.$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th><?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'Id'?></th>
								<th>Бренд</th>
								<th>Назва</th>
								<th>Постачальник</th>
								<th>Термін</th>
								<?php
								if($wl_user_types)
				                {
				                    foreach($wl_user_types as $group) 
				                        if($group->id > 2)
				                            echo("<td>Ціна для {$group->title}</td>");
				                }
				                else
				                    echo("<td>Ціна</td>");
								 ?>
								<th>Наявна кількість</th>
								<th>Доступна кількість</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php if($products && $cooperation)
                        	{ 
				                foreach ($products as $product) {
				                    $count = 0;
				                    echo("<tr>");
				                    echo('<td><a href="'.SITE_URL.'admin/'.$product->link.'">'.$product->article_show.'</a></td>');
                                    echo "<td>". ((!empty($product->manufacturer)) ? $product->manufacturer : '')."</td>";
                                    echo '<td><a href="'.SITE_URL.'admin/'.$product->link.'">'.html_entity_decode($product->name).'</a></td>';

				                    foreach ($cooperation as $storage) {
				                        if($storage->type == 'storage')
				                        {
				                        	if($wl_user_types)
				                            	$invoices = $this->load->function_in_alias($storage->alias2, '__get_Invoices_to_Product', ['id' => $product->id, 'user_type' => -1]);
				                            else
				                            	$invoices = $this->load->function_in_alias($storage->alias2, '__get_Invoices_to_Product', ['id' => $product->id, 'user_type' => 0]);
				                            if($invoices)
				                            {
				                                foreach ($invoices as $invoice) {
			                                        if($count > 0)
			                                            echo("</tr><tr><td></td><td></td><td></td>");

			                                        echo("<td>{$invoice->storage_name}</td>");
			                                        echo("<td>{$invoice->storage_time}</td>");
			                                        
			                                        if($wl_user_types)
										            {
										                $price_out = unserialize($invoice->price_out);
										                foreach($wl_user_types as $group) 
										                    if($group->id > 2)
										                    {
										                        $price_out_uah = round($price_out[$group->id] * $currency_USD, 2);
										                        echo("<td>\${$price_out[$group->id]}<br>");
										                        echo("<strong>{$price_out_uah} грн</strong></td>");
										                    }
										            }
										            else
										                echo("<td>{$invoice->price_out} {$invoice->currency}");
										            echo("<td>{$invoice->amount}</td>");
										            echo("<td><strong>{$invoice->amount_free}</strong></td>");

										            $count++;
				                                }
				                            }
				                        }
				                    }

				                    if($count == 0)
			                    	{
			                    		$count = 4;
			                    		if($wl_user_types)
										{
											foreach($wl_user_types as $group) 
						                    if($group->id > 2) $count++;
										}
										else
											 $count++;
			                    		echo "<td colspan={$count}><strong>Товар на складі відсутній</strong></td>";
			                    	}
				                    echo("</tr>");
				                }
							} 
							else
							{
								$count = 7;
	                    		if($wl_user_types)
								{
									foreach($wl_user_types as $group) 
				                    if($group->id > 2) $count++;
								}
								else
									 $count++;
								echo "<tr><td colspan={$count}>Товар за артикулом <strong>".$this->data->get('article')."</strong> не знайдено</td></tr>";
							}
							?>
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