<form method="POST" action="<?=SITE_URL.$_SESSION['alias']->alias?>/notify" class="balance_notify">
	<h2>Подати інформацію про оплату (поповнення рахунку)</h2>
	<input type="number" name="amount" step="0.01" min="1" required placeholder="Сума в грн" oninput="debitReCount(this)" onchange="debitReCount(this)">
    <h4>Поповнено по курсу: <strong>$0</strong></h4>
	<textarea name="info" placeholder="Інформація до оплати"></textarea>
	<button>Відправити</button>
</form>
<table id="tsearch">
    <thead>
        <tr>
            <td style="width: 120px">Дата</td>
            <td>Дебет</td>
            <td>Кредит</td>
            <td>Баланс</td>
            <td class="nomob">Інформація</td>
        </tr>
    </thead>
    <tbody>
    	<?php if (!empty($payments)) {
    		foreach ($payments as $payment) {
    			if($payment->status != 3)
    			{
    				if($payment->status == 1)
	    				echo "<tr class='notify' title='Платіж очікує підтвердження адміністрацією'>";
	    			else
	    				echo "<tr>";
    					echo "<td>#".$payment->id.' '.date('d.m.Y H:i', $payment->date_add)."</td>";
    					if($payment->debit)
                        {
                            $payment->debit = round($payment->debit, 2);
    						echo "<td>\${$payment->debit}</td>";
                        }
    					else
    						echo "<td></td>";
    					if($payment->credit)
                        {
                            $payment->credit = round($payment->credit, 2);
    						echo "<td>\${$payment->credit}</td>";
                        }
    					else
    						echo "<td></td>";
                        $payment->balance = round($payment->balance, 2);
    					echo "<td>\${$payment->balance}</td>";
    					echo "<td>{$payment->action}</td>";
    				echo "</tr>";
    			}
    		}
    	} else echo "<tr><td colspan=5>Записи відсутні</td></tr>";?>
    </tbody>
</table>

<style type="text/css">
	.balance_notify {
		max-width: 400px;
		text-align: center;
	}
	.balance_notify input, textarea {
		width: 100%;
		padding: 8px;
		margin-bottom: 10px;
		border: 1px solid #10879b;
	}
	.balance_notify button {
		width: 50%;
		padding: 8px;
		color: #fff;
		background: #10879b;
		border: none;
	}
	.balance_notify button:hover { background: #085d6b }
	tr.notify { background: #fff387 }
    #tsearch td:last-child { max-width: 150px }
</style>

<script>
    function debitReCount(amount) {
        var debit = amount.value / <?=$_SESSION['currency']['USD']?>;
        $('.balance_notify h4 strong').text('$'+debit.toFixed(2));
    }
</script>