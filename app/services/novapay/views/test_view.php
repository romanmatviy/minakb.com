<form method="post">
	volume_weight: <input type="number" name="volume_weight" title="volume_weight" placeholder="volume_weight" step="0.0001" value="<?=$this->data->post("volume_weight")?>" required min="0">
	<br>
	weight: <input type="number" name="weight" title="weight" placeholder="weight" step="0.01" value="<?=$this->data->post("weight")?>" required min="0">
	<br>
	recipient_city: <input type="text" name="recipient_city" title="recipient_city" placeholder="recipient_city" value="<?=($recipient_city = $this->data->post("recipient_city")) ? $recipient_city : "db5c893b-391c-11dd-90d9-001a92567626"?>" required>
	<br>
	recipient_warehouse: <input type="text" name="recipient_warehouse" title="recipient_warehouse" placeholder="recipient_warehouse" value="<?=($recipient_warehouse = $this->data->post("recipient_warehouse")) ? $recipient_warehouse : "1ec09d35-e1c2-11e3-8c4a-0050568002cf"?>" required>
	<input type="submit" value="Test!">
</form>