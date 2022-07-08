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
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Історія оновлення від <?=date('d.m.Y H:i', $update->date)?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Виробник</th>
                                <th><?=($_SESSION['option']->productUseArticle) ? 'Артикул' : 'ID'?></th>
								<th>Ціна до</th>
								<th>Ціна після</th>
								<th>Кількість до</th>
								<th>Кількість після</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($products)) { 
                        		foreach($products as $product) { ?>
									<tr>
                                        <td><?=$product->manufacturer_name?></td>
										<td><a href="<?=SITE_URL.'admin/parts/'.$product->alias?>"><?=($_SESSION['option']->productUseArticle) ? $product->article : $product->product?></a></td>
										<td>$<?=$product->price_old?></td>
										<td>$<?=$product->price_new?></td>
										<td><?=$product->amount_old?></td>
										<td><?=$product->amount_new?></td>
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