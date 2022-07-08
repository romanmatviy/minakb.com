<div class="row">
	<div class="row search-row">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/search">
            <div class="col-lg-8 col-sm-8 search-col">
                <input type="text" name="article" class="form-control" placeholder="Артикул" value="<?=$this->data->get('article')?>" required="required">
            </div>
            <div class="col-lg-4 col-sm-4 search-col">
                <button class="btn btn-primary btn-search btn-block"><i class="fa fa-search"></i><strong> Знайти</strong></button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1) { ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>
					<?php } ?>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name .'. Пошук '.$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>
            <div class="panel-body">
            	<?php if(!$mainSearch) { ?>
            		<div class="alert alert-warning">
			            <p>Увага! Товар за артикулом <strong><?=$this->data->get('article')?></strong> не знайдено. <?=(empty($products)) ? 'Розширений пошук за аналогами результату не дав' : 'Віднайдено аналоги товару'?></p>
			        </div>
            	<?php } ?>
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Артикул</th>
								<th>Бренд</th>
								<th>Назва</th>
								<th>Постачальник</th>
								<th>Термін</th>
								<?php
								$groups = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
								foreach($groups as $group) 
				                    if($group->id > 2)
				                        echo("<th>Ціна для {$group->title}</th>");
								 ?>
								<th>Наявна кількість</th>
								<th>Доступна кількість</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	$currency_USD = $this->load->function_in_alias('currency', '__get_Currency', 'USD');
                        	$products_on_page = array();
                        	$analogs = array();
                        	$lviv_storages = array(4, 6); //товар з Львівського складу

                        	$markUps = array();

                        	if(!empty($products))
                        	{
                        		$productsPrice = array();

        		                $storages = array();
        		                $cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1');
			                    foreach ($cooperation as $storage) {
			                        if($storage->type == 'storage')
			                            $storages[] = $storage->alias2;
			                    }			                    

				                foreach ($products as $product) {
				                    $products_on_page[] = $product->id;

				                    $count = 0;
				                    if($product->analogs != '')
				                        foreach (explode(',', $product->analogs) as $analog) { 
				                        	$analog = $this->shop_model->makeArticle($analog);
				                            if($analog != '' && !in_array($analog, $analogs)) $analogs[] = $analog;
				                        }
				                    
				                    $product->invoices = $this->shop_model->getInvoices($product->id, $storages, -1);
				                    if($product->invoices)
				                    {
				                    	foreach ($product->invoices as $invoice) {
					                    	if(!isset($markUps[$invoice->storage]))
					                    		if($mus = $this->db->getAllDataByFieldInArray('s_shopstorage_markup', $invoice->storage, 'storage'))
										            foreach ($mus as $mu)
										                $markUps[$invoice->storage][$mu->user_type] = $mu->markup;
						            	}
						            	$price_out = $product->invoices[0]->price_in;
						            	if(isset($markUps[$product->invoices[0]->storage][3]))
	                						$price_out = round($product->invoices[0]->price_in * ($markUps[$product->invoices[0]->storage][3] + 100) / 100, 2);
						            	$productsPrice[$product->id] = $price_out;
				                    }
				                }

				                if(!empty($productsPrice))
				                {
				                    asort($productsPrice);
				                    foreach ($productsPrice as $id => $price)
				                        foreach ($products as $product) {
				                            if($product->id == $id)
				                            {
				                                $count = 0;
				                                foreach ($product->invoices as $invoice)
				                                    showProductRow($product, $invoice, $count, $lviv_storages, $currency_USD, $groups, $markUps);
				                                break;
				                            }
				                        }
				                }
				                
				                foreach ($products as $product) {
		                            if(empty($product->invoices))
		                            {
		                            	$count = 0;
			                    		showProductRow($product, false, $count, $lviv_storages, $currency_USD, $groups, $markUps);
		                            }
		                        }
							} 
							else
							{
								$count = 7;
								foreach($groups as $group) 
				                    if($group->id > 2) $count++;
								echo "<tr><td colspan={$count}>Товар за артикулом <strong>".$this->data->get('article')."</strong> не знайдено</td></tr>";
							}
							?>
                        </tbody>
                    </table>
	
					<?php if($cooperation && !empty($analogs))
					{
						$analogProducts = $productsPrice = array();
						?>
					<h4>Наявні аналоги</h4>
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Артикул</th>
								<th>Бренд</th>
								<th>Назва</th>
								<th>Постачальник</th>
								<th>Термін</th>
								<?php
								foreach($groups as $group) 
				                    if($group->id > 2)
				                        echo("<th>Ціна для {$group->title}</th>");
								 ?>
								<th>Наявна кількість</th>
								<th>Доступна кількість</th>
                            </tr>
                        </thead>
                        <tbody>
				            <?php foreach ($analogs as $analog) {
		                    if($analog = $this->shop_model->getProducts('%'.$analog))
		                    {

		                        foreach ($analog as $product) {

		                            if(in_array($product->id, $products_on_page))
		                                continue;

		                            $products_on_page[] = $product->id;
		                        
		                            $product->invoices = $this->shop_model->getInvoices($product->id, $storages, -1);
		                            if($product->invoices)
		                            {
		                                foreach ($product->invoices as $invoice)
		                                    if($invoice->amount_free > 0) {
		                                    	if(!isset($markUps[$invoice->storage]))
						                    		if($mus = $this->db->getAllDataByFieldInArray('s_shopstorage_markup', $invoice->storage, 'storage'))
											            foreach ($mus as $mu)
											                $markUps[$invoice->storage][$mu->user_type] = $mu->markup;
		                                        if(!isset($productsPrice[$product->id]))
		                                        {
		                                            $price_out = $invoice->price_in;
									            	if(isset($markUps[$invoice->storage][3]))
				                						$price_out = round($invoice->price_in * ($markUps[$invoice->storage][3] + 100) / 100, 2);
									            	$productsPrice[$product->id] = $price_out;
		                                        }
		                                    }
		                                $analogProducts[$product->id] = clone $product;
		                            }
		                        }
		                    }
		                }

		                if(!empty($productsPrice))
		                {
		                    asort($productsPrice);
		                    foreach ($productsPrice as $id => $price)
		                        foreach ($analogProducts as $product) {
		                            if($product->id == $id && !empty($product->invoices))
		                            {
		                                $count = 0;
		                                foreach ($product->invoices as $invoice)
		                                	if($invoice->amount_free > 0)
		                                		showProductRow($product, $invoice, $count, $lviv_storages, $currency_USD, $groups, $markUps);
		                                break;
		                            }
		                        }
		                }
						else
						{
							$count = 7;
							foreach($groups as $group) 
			                    if($group->id > 2) $count++;
							echo "<tr><td colspan={$count}>Товари відсутні</td></tr>";
						}
						?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>
                <a href="https://privatbank.ua/" target="_blank" class="pull-right">1 USD = <?=$currency_USD?> UAH</a>
                <?php
                $this->load->library('paginator');
                echo $this->paginator->get();
                ?>
            </div>
        </div>
    </div>
</div>

<?php 
function showProductRow($product, $invoice, &$count, $lviv_storages, $currency_USD, $groups, $markUps)
{
    $class = (isset($invoice->storage) && in_array($invoice->storage, $lviv_storages)) ? 'class="success"' : '';

    echo("<tr {$class}>");
    if($count == 0)
    {
        echo('<td><a href="'.SITE_URL.'admin/'.$product->link.'">'.$product->article.'</a></td>');
        echo "<td>{$product->manufacturer_name}</td>";
        echo '<td><a href="'.SITE_URL.'admin/'.$product->link.'">'.html_entity_decode($product->name).'</a></td>';
    }
    else
        echo "<td></td><td></td><td></td>";

    $count++;

    if(!empty($invoice))
    {
	 	echo("<td>{$invoice->storage_name}</td>");
	    echo("<td>{$invoice->storage_time}</td>");
	    
	    $price_out = false;

		if(!is_numeric($invoice->price_out))
	    	$price_out = unserialize($invoice->price_out);
	    
	    foreach($groups as $group) 
	        if($group->id > 2)
	        {
	            if($price_out)
	            {
	                $price_out_uah = round($price_out[$group->id] * $currency_USD, 2);
	                echo("<td>\${$price_out[$group->id]}<br>");
	                echo("<strong>{$price_out_uah} грн</strong></td>");
	            }
	            elseif(isset($markUps[$invoice->storage][$group->id]))
	            {
	                $price_out_usd = round($invoice->price_in * ($markUps[$invoice->storage][$group->id] + 100) / 100, 2);
	                $price_out_uah = round($price_out_usd * $currency_USD, 2);
	                echo("<td>\${$price_out_usd}<br>");
	                echo("<strong>{$price_out_uah} грн</strong></td>");
	            }
	        }
	    echo("<td>{$invoice->amount}</td>");
	    echo("<td><strong>{$invoice->amount_free}</strong></td>");
	}
	else
    {
		$cols = 4;
		foreach($groups as $group) 
            if($group->id > 2) $cols++;
		echo "<td colspan={$cols}><strong>Товар на складі відсутній</strong></td>";
    }
    
    echo("</tr>");
}
?>



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