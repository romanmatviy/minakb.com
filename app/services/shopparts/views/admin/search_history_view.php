<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="table-responsive">
            <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                <thead>
                    <tr>
						<th>Дата</th>
                        <th title="Останній перегляд">Перегляд</th>
                        <th>Переглядів</th>
                        <th>Артикул</th>
                        <th>Товар</th>
                        <th>Клієнт</th>
                        <th>Дати останніх покупок</th>
                    </tr>
                </thead>
                <tbody>
				<?php if($search_history) 
					foreach($search_history as $search) {
                        $buy = false;
                        if($search->product_id > 0) $buy = $this->db->getQuery("SELECT cart, date FROM s_cart_products WHERE user = {$search->user} AND product = {$search->product_id} ORDER BY date DESC LIMIT 3", 'array');
                ?>
                    <tr>
                        <td><?=date("d.m.Y", $search->date)?></td>
                        <td><?=date("H:i", $search->last_view)?></td>
                        <td><?=$search->count_per_day?></td>
                        <td><a href="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/search?<?=($search->product_id > 0) ? 'id='.$search->product_id : 'article='.$search->product_article?>">
                            <?php 
                            if($search->product_id > 0)
                                echo '<i class="fa fa-check" title="Товар знайдено"></i> '.$search->article;
                            else
                                echo $search->product_article;
                            ?>
                            </a>
                        </td>
                        <td><?=$search->name?></td>
                        <td><a href="<?=SITE_URL?>admin/wl_users/<?=$search->user_email?>"><?=$search->user_name?></a>
                        <td>
                            <?php if($buy) foreach ($buy as $cart) {
                                echo '<a href="'.SITE_URL.'admin/cart/'.$cart->cart.'" target="_blank">'.date('d.m.Y', $cart->date).'</a>';
                            } else echo "Відсутні"; ?>
                        </td>
                    </tr>
                <?php } ?>
				</tbody>
			</table>
		</div>
		<?php
        $this->load->library('paginator');
        echo $this->paginator->get();
        ?>
	</div>
</div>