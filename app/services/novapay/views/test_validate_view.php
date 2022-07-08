<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>NovaPay test validate</title>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,700,300,600,400&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<link href="<?=SITE_URL?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
	<div class="container">
		<h1>test validate</h1>
		<h4>novapay_public_key:</h4>
		<pre><?php echo file_get_contents($novapay_public_key); ?></pre>
		<form action="/novapay/test_validate" method="post">
			<input type="text" class="form-control" name="xsign" placeholder="x-sign" value="<?=$this->data->post('xsign')?>" required> <br>
			<textarea name="text" class="form-control" placeholder="text to check" required><?=$this->data->post('text')?></textarea> <br>
			<button>Test!</button>
		</form>

		<?php if($this->data->post('xsign')) {
			$xsign = $_POST['xsign'];
			$data_string = $_POST['text'];
			$binary_signature = base64_decode($xsign);
			$public_key = openssl_pkey_get_public(file_get_contents($novapay_public_key));
			// $data_string = json_encode(json_decode(trim($data_string))) ;
			$result = openssl_verify($data_string, $binary_signature, $public_key, OPENSSL_ALGO_SHA1);
		 ?>
			<hr style="margin: 25px 0">
			<h4>input X-Sign (signature base64)</h4>
			<pre><?=$xsign?></pre>
			<h4>input text to test ($data_string, Content-Length: <?=mb_strlen($data_string, "utf8")?>)</h4>
			<pre><?php var_dump($data_string)?></pre>

			<p>$binary_signature = base64_decode($xsign); <br> $public_key = openssl_pkey_get_public(file_get_contents($novapay_public_key));</p>
			<h4>openssl_verify($data_string, $binary_signature, $public_key, OPENSSL_ALGO_SHA1)</h4>
			<?php 

			 ?>
			 <pre><?php var_dump($result); ?></pre>
		<?php } ?>
	</div>


	<script src="<?=SITE_URL?>assets/jquery/jquery-1.9.1.min.js"></script>
	<script src="<?=SITE_URL?>assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>