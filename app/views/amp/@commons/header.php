<header class="header">
	<a href="<?=SITE_URL?>amp">
		<amp-img src="<?=IMG_PATH?>warendorf-header.png" alt="Warendorf" height="24" width="200"></amp-img>
	</a>

	<button on="tap:sidebar.open" class="nav-trigger">
		<div></div><div></div><div></div>
	</button>
</header>

<amp-sidebar id="sidebar" layout="nodisplay" side="right">
    <ul class="prime-nav">
        <button on="tap:sidebar.close" class="close">×</button>
        <?php if ($_SESSION['alias']->id == 1) {
            if (!$this->userCan()) { ?><li><a href="#home" class="active">HOME</a></li><?php } ?>
            <li><a href="#standorte">Standorte</a></li>
            <li><a href="#inspiration">INSPIRATIONEN</a></li>
            <li><a href="#services">UNSER SERVICE</a></li>
            <li><a href="#accessories">AUSSTATTUNG</a></li>
            <li><a href="#about">ÜBER WARENDORF</a></li>
        <?php } elseif (in_array($_SESSION['alias']->alias, array('berlin', 'dusseldorf', 'hamburg', 'werksstudio'))) { ?>
            <li><a href="#calendar">TERMINKALENDER</a></li>
            <li><a href="#inspiration">INSPIRATIONEN</a></li>
            <li><a href="#team">TEAM</a></li>
            <li><a href="#accessories">AUSSTATTUNG</a></li>
            <li><a href="#about">ÜBER WARENDORF</a></li>
            <li><a href="#services">UNSER SERVICE</a></li>
        <?php } else {
            if (!$this->userCan()) { ?>
                <li><a href="<?=SITE_URL?>amp" class="active">HOME</a></li>
            <?php } ?>
            <li><a href="<?= SITE_URL ?>#services">UNSER SERVICE</a></li>
            <li><a href="<?= SITE_URL ?>#inspiration">INSPIRATIONEN</a></li>
            <li><a href="<?= SITE_URL ?>#standorte">Standorte</a></li>
            <li><a href="<?= SITE_URL ?>#accessories">AUSSTATTUNG</a></li>
            <li><a href="<?= SITE_URL ?>#about">ÜBER WARENDORF</a></li>
        <?php } ?>
    </ul>
</amp-sidebar>