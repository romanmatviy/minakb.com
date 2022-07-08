<?php if($_SESSION['user']->status == 4) { ?>
	<div class="alert alert-warning">
		<h4>Увага! Профіль на перевірці адміністрацією порталу <?=SITE_NAME?></h4>
		<p>Ви вже можете додавати товари, підготувати свій профіль продавця для Ваших покупців. Чим більше інформації додано, тим швидше відбудеться модерація та публікація інформації для всіх</p>
	</div>
<?php } ?>

<?php if(!empty($products)) { ?>
	<a href="<?=SITE_URL.$_SESSION['alias']->alias?>/add" class="btn btn-success pull-right"><i class="far fa-plus-square"></i> Додати товар</a>
	<h4>Всіх товарів: <?=$_SESSION['option']->paginator_total ?></h4>
	<div class="clearfix"></div>
	<table>
		<thead>
			<tr>
				<td>Товар</td>
				<td>Дії</td>
				<td>Ціна</td>
				<td>Статус</td>
				<td>Наявність</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($products as $product) { ?>
				<tr>
					<td><a href="<?=SITE_URL.$product->link?>"><?=$product->name?> <i class="fab fa-telegram-plane"></i></a></td>
					<td><a href="<?=SITE_URL.'seller/analytics/'.$product->alias?>" class="btn btn-success"><i class="fas fa-chart-area"></i> Аналітика</a> <a href="<?=SITE_URL.$product->link?>?edit" class="btn btn-warning"><i class="fas fa-paint-brush"></i> Редагувати</a></td>
					<td><?=$product->price_format?>/<?=$product->cina_za ?? ''?></td>
					<td><?php switch ($product->active) {
						case -2:
							echo "Наповнення товару";
							break;
						case -1:
							echo "На перевірці";
							break;
						case 0:
							echo "Відключено";
							break;
						case 1:
							echo "Активний";
							break;
						
						default:
							echo "Відключено згідно тарифу";
							break;
					} ?></td>
					<td><?=$product->availability_name?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
<?php } else { ?>
	<div class="d-flex h-center v-center column">
		<p style="margin: 15px">Товари відсутні</p>
		<a href="<?=SITE_URL.$_SESSION['alias']->alias?>/add" class="btn"><i class="far fa-plus-square"></i> Додайте Ваш перший товар</a>
	</div>
<?php } ?>