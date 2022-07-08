<section id="contact" class="bg-fixed wpb_row vc_row-fluid">
    <h3 class="color">ÜBER Warendorf Retail GmbH</h3>
    <address><?=$this->text('footer text', 0)?></address>

    <h3 class="color">ZUSÄTZLICHE INFOS</h3>
    <address>
        <a href="<?=SITE_URL?>impressum/amp"><?=$this->text('Impressum', 0)?></a>
        <a href="<?=SITE_URL?>datenschutzerklärung/amp"><?=$this->text('Datenschutzerklärung', 0)?></a>
    </address>

    <?php if(in_array($_SESSION['alias']->alias, array('berlin', 'dusseldorf', 'hamburg', 'werksstudio'))) { ?>
        <amp-img src="<?=IMG_PATH?>logos/warendorf-<?= $_SESSION['alias']->alias ?>.png" alt="warendorf"
                 class="footerlogo" height="59" width="175"></amp-img>
    <?php } else { ?>
        <amp-img src="<?=IMG_PATH?>warendorf-footer-01.png" alt="warendorf" class="footerlogo" height="59"
                 width="175"></amp-img>
    <?php } ?>

    <address>
        <?php if(in_array($_SESSION['alias']->alias, array('berlin', 'dusseldorf', 'hamburg', 'werksstudio'))) { ?>
            <strong>Warendorf Retail GmbH</strong>
            <?=$this->text($_SESSION['alias']->alias.' city', 0)?>
            <?=$this->text($_SESSION['alias']->alias.' address', 0)?>
            <strong class="color">Tel: </strong> <?=$this->text($_SESSION['alias']->alias.' tel', 0)?>
            <strong class="color">Email: </strong> <?=$this->text($_SESSION['alias']->alias.' email', 0)?>
        <?php } else { ?>
            <h3 class="color"><a href="<?=SITE_URL?>amp/berlin">Küchenstudio Berlin</a></h3>
            <h3 class="color"><a href="<?=SITE_URL?>amp/dusseldorf">Küchenstudio Düsseldorf</a></h3>
            <h3 class="color"><a href="<?=SITE_URL?>amp/hamburg">Küchenstudio Hamburg</a></h3>
            <h3 class="color"><a href="<?=SITE_URL?>amp/werksstudio">Werksstudio Warendorf</a></h3>
        <?php } ?>
    </address>
</section>
<div class="subfooter">
  © Copyright <?=date('Y')?> - <strong class="color">Warendorf Retail GmbH</strong>
    <a href="http://webspirit.com.ua" target="_blank">
    <amp-img src="<?= SERVER_URL ?>style/admin/images/WebSpirit_logo_mini.png" alt="WebSpirit Creative Agency logo"
               height="17" width="21"></amp-img>
      WebSpirit Creative Agency
  </a>
</div>