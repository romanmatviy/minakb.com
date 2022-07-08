<div class="row">
	<div class="row search-row">
        <form>
            <div class="col-lg-8 col-sm-8 search-col">
                <input type="text" name="<?=($_SESSION['option']->productUseArticle) ? 'article' : 'id'?>" class="form-control" placeholder="<?=($_SESSION['option']->productUseArticle) ? 'Артикул' : 'ID'?>" value="<?=$this->data->get('article')?>" required="required">
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
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> Додати наявність</a>
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-warning btn-xs"><i class="fa fa-refresh"></i> Оновити прайс</a>
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування складу</a>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Прихідні накладні потоварно</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Накладна №</th>
                                <th>Виробник</th>
                                <th>Товар</th>
								<th>Загальна наявність / Доступно</th>
								<?php if($_SESSION['option']->markUpByUserTypes == 1) {
									$groups = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
									foreach($groups as $group) if($group->id > 2) {
									?>
									<th>Ціна для <strong><?=$group->title?></strong></th>
								<?php }
								 } else { ?>
									<th>Ціна вихідна</th>
								<?php } ?>
								<th>Остання операція</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($invoices)) { 
                        		$markUps = array();
						        if($mus = $this->db->getAllDataByFieldInArray('s_shopstorage_markup', $_SESSION['alias']->id, 'storage'))
						            foreach ($mus as $mu) {
						                $markUps[$mu->user_type] = $mu->markup;
						            }
                        		foreach($invoices as $product) { ?>
									<tr>
										<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$product->id?>" class="btn btn-info btn-xs">#<?=$product->id?></a></td>
										<td><?=$product->info->options['1-manufacturer']->value ?? ''?></td>
										<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$product->id?>">
											<strong><?=$_SESSION['option']->productUseArticle ? $product->info->article_show : '#'.$product->info->id?></strong> <?=$product->info->name?> </a>
										</td>
										<td title="Резервовано: <?=($product->amount_reserved != '') ? $product->amount_reserved : 0?>"><?=$product->amount?> / <b><?=$product->amount_free?></b></td>
										<?php if($_SESSION['option']->markUpByUserTypes == 1) {
											$price_out = $product->price_in;
											if($product->price_out != 0)
												$product->price_out = unserialize($product->price_out);
											foreach($groups as $group)
												if($group->id > 2) {
													if(is_array($product->price_out) && isset($product->price_out[$group->id]))
														$price_out = $product->price_out[$group->id];
													elseif(isset($markUps[$group->id]))
														$price_out = round($product->price_in * ($markUps[$group->id] + 100) / 100, 2);
													echo "<td>{$price_out}</td>";
												}
											if(is_array($product->price_out) && isset($product->price_out[0]))
												$price_out = $product->price_out[0];
											elseif(isset($markUps[0]))
												$price_out = round($product->price_in * ($markUps[0] + 100) / 100, 2);
											echo "<th>{$price_out} {$product->currency}</th>";
										}
										else
										{
											if($product->price_out == 0)
											{
												$product->price_out = $product->price_in;
												if(!empty($product->markup))
													$product->price_out *= (1 + $product->markup / 100);
											}
											echo("<th>{$product->price_out} {$product->currency}</th>");
										}
                                        ?>
										<td><?=($product->date_out > 0) ? date("d.m.Y H:i", $product->date_out) : 'Відсутня'?></td>
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