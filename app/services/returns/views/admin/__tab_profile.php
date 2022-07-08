<div class="col-md-12 ui-sortable">
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Заявки на повернення товару</h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Статус</th>
                            <th>Замовлення</th>
                            <th>Товар</th>
                            <th>Кількість</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                    	<?php if($returns) foreach($returns as $return) { ?>
						<tr <?=($return->status == 1) ? 'class="warning"' : ''?>>
                            <td><a href="<?=SITE_URL?>admin/returns/<?= $return->id ?>">Детально #<?= $return->id ?></a></td>
                            <td>
                                <?php switch ($return->status) {
                                    case 1:
                                        if($return->manager > 0)
                                            echo "Повернення дозволено";
                                        else
                                            echo "Очікує обробки";
                                        break;
                                    case 2:
                                        echo "Підтверджено";
                                        break;
                                    case 3:
                                        echo "Скасовано";
                                        break;
                                } ?>
                            </td>
                            <td><?= $return->cart_id ?></td>
                            <td><strong><?=$return->product_article?></strong> <?=$return->product_manufacturer.' '.$return->product_name?></td>
                            <td><?= $return->quantity ?></td>
							<td><?= date('d.m.Y H:i', $return->date_add) ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>