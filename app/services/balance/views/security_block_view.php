<div class="container" style="text-align: center;">
	<img src="<?=SERVER_URL?>style/<?=$_SESSION['alias']->alias?>/block-security-logo.jpg" alt="block-security-logo">
	<h1><?=$this->text('Система безпеки: Ваш акаунт (обліковий запис) заблоковано!')?></h1>
	<h3><?=$this->text('Цифровий підпис балансу користувача не коректний!')?></h3>
	<h2><?=$this->text('Зверніться до адміністрація для розблокування')?> <a href="mailto:<?=SITE_EMAIL?>"><?=SITE_EMAIL?></a>. <?=$this->text('Вибачте за незручності')?></h2>
</div>