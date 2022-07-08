<?php
$showStorages = true;
$count_products = 0;
$wl_user_types = false;
// $wl_user_types = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');

foreach ($storages as $storage) {
    // $invoice_where = array('id' => $invoice_to_product->id, 'user_type' => -1);
    $invoice_where = array('id' => $invoice_to_product->id, 'user_type' => 0);
    $invoices = $this->load->function_in_alias($storage, '__get_Invoices_to_Product', $invoice_where);
    if($invoices)
    {
        foreach ($invoices as $invoice) {
            if($showStorages)
            {
                echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
                echo("<tr>");
                echo("<td></td>");
                echo("<td>Постачальник</td>");
                echo("<td>Термін</td>");
                if($wl_user_types)
                {
                    foreach($wl_user_types as $group) 
                        if($group->id > 2)
                            echo("<td>Ціна для {$group->title}</td>");
                }
                else
                    echo("<td>Ціна</td>");
                echo("<td>Наявна кількість</td>");
                echo("<td>Доступна кількість</td>");
                echo("</tr>");
                $showStorages = false;
            }
            echo("<tr>");
            echo("<td><a href='/admin/{$invoice->storage_alias}/{$invoice->id}'>Накладна #{$invoice->id}</a></td>");
            echo("<td><strong>{$invoice->storage_name}</strong></td>");
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
                echo("<td><strong>{$invoice->price_out} {$invoice->currency}</strong></td>");
            echo("<td>{$invoice->amount}</td>");
            echo("<td><strong>{$invoice->amount_free}</strong></td>");
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
        <h4><?=$invoice_to_product->article_show?> <?=$invoice_to_product->name?></h4>
        <p>Увага! Товар відсутній на складі.</p>
    </div>
<?php
}
?>