<div class="row">
	<div class="panel panel-inverse">
	    <div class="panel-heading">
	        <h4 class="panel-title"><?=!empty($path) ? 'Аналіз вхідного файлу (перші 20 товарів)' : 'Результат імпорту (перші 200 товарів)'?></h4>
	    </div>
	    <div class="panel-body">
	    	<?php if(!empty($path)) { ?>
	    	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/import/go" method="POST">
        		<?php foreach(['path', 'col_id', 'col_price', 'row_start'] as $key) { ?>
	        		<input type="hidden" name="<?=$key?>" value="<?=$$key?>">
	        	<?php } ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-lg btn-warning"><i class="fa fa-upload" aria-hidden="true"></i> Імпорт</button>
                </div>
	        </form>
	    	<?php } ?>

            <table class="table table-striped table-bordered m-t-15">
				<tr>
					<th>ID товару</th>
					<th>Артикул</th>
					<th>Товар</th>
					<th>Ціна до</th>
					<th>Ціна після</th>
				</tr>
				<?php if(empty($products)) {
					$ids = [];
					foreach ($file as $rowIndex => $row) 
					{
						if($rowIndex < $row_start)
							continue;
						$id = (int) $row[$col_id];
						if(!empty($id) && $id > 0)
							$ids[] = $id;
					}
					if(!empty($ids))
					{
						$where_ntkd = ['alias' => '#p.wl_alias', 'content' => '#p.id'];
						if($_SESSION['language'])
							$where['language'] = $_SESSION['language'];
						$products = $this->db->select('s_shopshowcase_products as p', 'id, article_show, price', $ids)
											->join('wl_ntkd', 'name', $where_ntkd)
											->get('array');
					}
				}
				$max = !empty($path) ? 20 : 200;
				foreach ($file as $rowIndex => $row) 
				{
					if($rowIndex < $row_start)
						continue;

					$id = (int) $row[$col_id];
					$price_out = (double) $row[$col_price];
					$article_show = $product_name = $price_in = "";
					$class = 'danger';
					$title = 'Товар не знайдено';
					foreach ($products as $product) {
						if($product->id == $id)
						{
							$article_show = $product->article_show;
							$product_name = $product->name;
							$price_in = $product->price;
							$class = $product->price != $price_out ? 'warning' : '';
							$title = $product->price != $price_out ? 'Зміна ціни' : '';
							break;
						}
					}
					echo "<tr class='{$class}' title='{$title}'>";
						echo "<td>{$id}</td>";
						echo "<td>{$article_show}</td>";
						echo "<td>{$product_name}</td>";
						echo "<td>{$price_in}</td>";
						echo "<td>{$price_out}</td>";
					echo "</tr>";

					if($rowIndex >= $max)
						break;
				}
				?>
            </table>
	    </div>
	</div>
</div>