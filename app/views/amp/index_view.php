<section id="banner">
    <div class="cities">Berlin<br> Düsseldorf <br> Hamburg <br> Warendorf</div>
    <h2>Exklusive Küchenstudios</h2>
    <a href="#standorte">Standort wählen</a>
</section>

<section id="standorte">
    <h1>Unsere Standorte</h1>
    <div class="separator"></div>
    <div class="wrap">
        <div class="masonry">
            <a href="<?=SITE_URL?>berlin/amp">
                <amp-img src="<?=IMG_PATH?>logos/warendorf-berlin.png" alt="Küchenstudio Berlin" title="Küchenstudio Berlin"
                         width="176" height="58"></amp-img>
            </a>
            <p>Warendorf Retail GmbH<br>
                <?=$this->text('berlin city', 0)?><br>
                <?=$this->text('berlin address', 0)?><br>
                <a href="tel:<?=$this->text('berlin tel', 0)?>"><?=$this->text('berlin tel', 0)?></a><br>
                <a href="mailto:<?=$this->text('berlin email', 0)?>"><?=$this->text('berlin email', 0)?></a>
            </p>
            <a href="<?=SITE_URL?>berlin/amp" class="btn-line btn-fullwidth">Zum Standort</a>
        </div>
        <div class="masonry">
            <a href="<?=SITE_URL?>dusseldorf/amp">
                <amp-img src="<?=IMG_PATH?>logos/warendorf-dusseldorf.png" alt="Küchenstudio Düsseldorf"
                         title="Küchenstudio Düsseldorf" width="232" height="58"></amp-img>
            </a>
            <p> Warendorf Retail GmbH<br>
                <?=$this->text('dusseldorf city', 0)?><br>
                <?=$this->text('dusseldorf address', 0)?><br>
                <a href="tel:<?=$this->text('dusseldorf tel', 0)?>"><?=$this->text('dusseldorf tel', 0)?></a><br>
                <a href="mailto:<?=$this->text('dusseldorf email', 0)?>"><?=$this->text('dusseldorf email', 0)?></a>
            </p>
            <a href="<?=SITE_URL?>dusseldorf/amp" class="btn-line btn-fullwidth">Zum Standort</a>
        </div>
        <div class="masonry">
            <a href="<?=SITE_URL?>hamburg/amp">
                <amp-img src="<?=IMG_PATH?>logos/warendorf-hamburg.png" alt="Küchenstudio Hamburg" title="Küchenstudio Hamburg"
                         height="58" width="200"></amp-img>
            </a>
            <p>Warendorf Retail GmbH<br>
                <?=$this->text('hamburg city', 0)?><br>
                <?=$this->text('hamburg address', 0)?><br>
                <a href="tel:<?=$this->text('hamburg tel', 0)?>"><?=$this->text('hamburg tel', 0)?></a><br>
                <a href="mailto:<?=$this->text('hamburg email', 0)?>"><?=$this->text('hamburg email', 0)?></a>
            </p>
            <a href="<?=SITE_URL?>hamburg/amp" class="btn-line btn-fullwidth">Zum Standort</a>
        </div>
        <div class="masonry">
            <a href="<?=SITE_URL?>werksstudio/amp">
                <amp-img src="<?=IMG_PATH?>logos/warendorf-werksstudio.png" alt="Werksstudio Warendorf"
                         title="Werksstudio Warendorf" height="58" width="176"></amp-img>
            </a>
            <p>Warendorf Retail GmbH<br>
                <?=$this->text('werksstudio city', 0)?><br>
                <?=$this->text('werksstudio address', 0)?><br>
                <a href="tel:<?=$this->text('werksstudio tel', 0)?>"><?=$this->text('werksstudio tel', 0)?></a><br>
                <a href="mailto:<?=$this->text('werksstudio email', 0)?>"><?=$this->text('werksstudio email', 0)?></a>
            </p>
            <div class="spacer-single"></div>
            <a href="<?=SITE_URL?>werksstudio/amp" class="btn-line btn-fullwidth">Zum Standort</a>
        </div>
    </div>
</section>

<?php
$ins = $this->load->function_in_alias('kuechen', '__get_Articles', array('limit' => 12));
$group = $this->load->function_in_alias('kuechen', '__get_Groups');
?>
<section id="inspiration">
    <h1 class="headproject">INSPIRATIONEN</h1>
    <div class="separator"></div>
    <ul id="filters">
        <?php if(!empty($group)) foreach ($group as $g) {?>
            <li><a href="<?=SITE_URL.$g->link?>/amp"><?=$g->name?></a></li>
        <?php } ?>
    </ul>
     <div id="gallery">
        <?php if (!empty($ins)) foreach ($ins as $in) { ?>
            <div class="item group<?= $in->group ?>">
                <div class="picframe">
                    <amp-img layout="responsive" src="<?=IMG_PATH . $in->m_photo?>" width="480" height="324"
                         alt="<?= html_entity_decode(htmlspecialchars_decode($in->name), ENT_QUOTES) ?>">
                    </amp-img>
                    <a href="<?=SITE_URL.$in->link?>/amp">
                        <?= html_entity_decode(htmlspecialchars_decode($in->name), ENT_QUOTES) ?>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>
<section class="bg-yellow">
    <a href="<?=SERVER_URL?>kuechen/amp" class="btn-line-black btn-big">SIEHE ALLE INSPIRATIONEN</a>
</section>

<section id="services">
    <h1> UNSER SERVICE</h1>
    <div class="separator"></div>
    <?php $articles = $this->load->function_in_alias('services', '__get_Articles');
        $i = 1; foreach ($articles as $a) { ?>
        <div class="services__block services__block--<?=$i?>">
            <div class="services__mask">
                <h3 class="color"><?= html_entity_decode(htmlspecialchars_decode($a->name)) ?></h3>
                <div>
                    <?=html_entity_decode(htmlspecialchars_decode($a->text), ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>
    <?php $i++; } ?>
</section>

<?php $aus = $this->load->function_in_alias('ausstattung', '__get_Articles', array('limit' => 9)); ?>
<section id="accessories">
    <h1 class="headproject">AUSSTATTUNG</h1>
    <div class="separator"></div>
    <div id="gallery">
        <?php $i = 0; if (!empty($aus)) foreach ($aus as $in) { ?>
            <div class="item group<?= $in->group ?>">
                <div class="picframe">
                    <amp-img src="<?= IMG_PATH . $in->m_photo ?>" layout="responsive" width="480" height="324"
                         alt="<?= html_entity_decode(htmlspecialchars_decode($in->name), ENT_QUOTES) ?>">
                    </amp-img>
                    <a href="<?=SITE_URL.$in->link ?>/amp">
                        <?= html_entity_decode(htmlspecialchars_decode($in->name), ENT_QUOTES) ?>
                    </a>
                </div>
            </div>
        <?php $i++; if ($i > 8) break; } ?>
    </div>
</section>

<section class="partners">
    <ul>
        <li><amp-img src="<?=IMG_PATH?>partners/berbel.png" alt="berbel" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/blanco.png" alt="blanco" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/blauwasser.png" alt="blauwasser" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/bora.png" alt="bora.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/dornbracht.png" alt="dornbracht.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/eisinger.png" alt="eisinger.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/franke.png" alt="franke.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/gaggenau.png" alt="gaggenau.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/jaksch.png" alt="jaksch.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/kff.png" alt="kff.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/liebherr.png" alt="liebherr.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/miele.png" alt="miele.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/ofa-line.png" alt="ofa-line.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/quooker.png" alt="quooker.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/ispekva.png" alt="ispekva.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/vzug.png" alt="vzug.png" width="150" height="75"></amp-img></li>
        <li><amp-img src="<?=IMG_PATH?>partners/vileroy.png" alt="vileroy.png" width="150" height="75"></amp-img></li>
    </ul>
</section>

<section id="about">
    <h1>ÜBER WARENDORF</h1>
    <div class="separator"></div>
    <blockquote>
        <?= strip_tags(html_entity_decode($_SESSION['alias']->text, ENT_QUOTES)) ?>
    </blockquote>
</section>