<?php
$showStorages = true;
$count_products = 0;
$products_on_page = array($product->id);
$groups = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
$currency_USD = $this->load->function_in_alias('currency', '__get_Currency', 'USD');

foreach ($storages as $storage) {
    $invoice_where = array('id' => $product->id, 'user_type' => -1);
    $invoices = $this->load->function_in_alias($storage, '__get_Invoices_to_Product', $invoice_where);
    if($invoices)
    {
        $markUps = array();
        if($mus = $this->db->getAllDataByFieldInArray('s_shopstorage_markup', $storage, 'storage'))
            foreach ($mus as $mu) {
                $markUps[$mu->user_type] = $mu->markup;
            }
        foreach ($invoices as $invoice) {
            if($showStorages)
            {
                echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
                echo("<tr>");
                echo("<td>Постачальник</td>");
                echo("<td>Термін</td>");
                foreach($groups as $group) 
                    if($group->id > 2)
                        echo("<td>Ціна для {$group->title}</td>");
                echo("<td>Наявна кількість</td>");
                echo("<td>Доступна кількість</td>");
                echo("<td></td>");
                echo("</tr>");
                $showStorages = false;
            }
            echo("<tr>");
            echo("<td>{$invoice->storage_name}</td>");
            echo("<td>{$invoice->storage_time}</td>");
            $price_out = false;
            if($invoice->price_out != 0)
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
                    else
                    {
                        $price_out_usd = round($invoice->price_in * ($markUps[$group->id] + 100) / 100, 2);
                        $price_out_uah = round($price_out_usd * $currency_USD, 2);
                        echo("<td>\${$price_out_usd}<br>");
                        echo("<strong>{$price_out_uah} грн</strong></td>");
                    }
                }
            echo("<td>{$invoice->amount}</td>");
            echo("<td><strong>{$invoice->amount_free}</strong></td>");
            echo("<td></td>");
            echo("</tr>");
            $count_products++;
        }
    }
}
if(!$showStorages)
{
    echo("</table></div>");
}
if($count_products == 0)
{
    ?>
    <div class="alert alert-danger">
        <h4><?=$product->article?> <?=$product->name?></h4>
        <p>Увага! Товар відсутній на складі.</p>
    </div>
<?php
}

if($product->analogs != '') { $count_products = 0; ?>
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
        <?php foreach (explode(',', $product->analogs) as $analog_article) if($analog_article != '') {
            if($analogs = $this->shop_model->getProducts('%'.$analog_article))
            {
                foreach ($analogs as $analog) {
                    if(in_array($analog->id, $products_on_page)) continue;

                    $count = 0;

                    foreach ($cooperation as $storage) {
                        if($storage->type == 'storage')
                        {
                            $invoice_where = array('id' => $analog->id, 'user_type' => -1);
                            $invoices = $this->load->function_in_alias($storage->alias2, '__get_Invoices_to_Product', $invoice_where);
                            if($invoices)
                            {
                                $markUps = array();
                                if($mus = $this->db->getAllDataByFieldInArray('s_shopstorage_markup', $storage->alias2, 'storage'))
                                    foreach ($mus as $mu) {
                                        $markUps[$mu->user_type] = $mu->markup;
                                    }
                                foreach ($invoices as $invoice) if($invoice->amount > 0) {
                                    if($count > 0)
                                        echo("</tr><tr><td></td><td></td><td></td>");
                                    else
                                    {
                                        echo("<tr>");
                                        echo('<td><a href="'.SITE_URL.'admin/'.$analog->link.'">'.$analog->article.'</a></td>');
                                        echo "<td>{$analog->manufacturer_name}</td>";
                                        echo '<td><a href="'.SITE_URL.'admin/'.$analog->link.'">'.html_entity_decode($analog->name).'</a></td>';
                                    }

                                    echo("<td>{$invoice->storage_name}</td>");
                                    echo("<td>{$invoice->storage_time}</td>");
                                    
                                    $price_out = false;
                                    if($invoice->price_out != 0)
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
                                            else
                                            {
                                                $price_out_usd = round($invoice->price_in * ($markUps[$group->id] + 100) / 100, 2);
                                                $price_out_uah = round($price_out_usd * $currency_USD, 2);
                                                echo("<td>\${$price_out_usd}<br>");
                                                echo("<strong>{$price_out_uah} грн</strong></td>");
                                            }
                                        }
                                    echo("<td>{$invoice->amount}</td>");
                                    echo("<td><strong>{$invoice->amount_free}</strong></td>");

                                    $count++;
                                    $count_products++;
                                }
                            }
                        }
                    }
                    echo("</tr>");
                }
            }
        }
        if($count_products == 0)
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
<a href="https://privatbank.ua/" target="_blank" class="pull-right">1 USD = <?=$currency_USD?> UAH</a>