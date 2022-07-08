<link rel="stylesheet" type="text/css" href="<?=SERVER_URL?>style/<?=$_SESSION['alias']->alias?>/faq.css">
<main id="faq">
    <div class="container">
        <h1><?=$_SESSION['alias']->name?></h1>
        <div class="content">
            <?php if($groups) {
                foreach($groups as $group) { ?>
                    <h2><?=$group->name?></h2>
                        <?php if($faqs) { 
                            foreach($faqs as $faq) { 
                                if($faq->group == $group->id) {
                                    $active = '';
                                    if($this->data->uri(1) == $group->alias && $this->data->uri(2) == $faq->alias)
                                        $active = 'open'; ?>
                                    <details id="<?=$faq->id.'-'.$faq->alias?>" <?=$active?>>
                                        <summary><?=$faq->question?></summary>
                                        <?=html_entity_decode($faq->answer)?>
                                    </details>
                        <?php   }
                            }
                        } ?>
                    <br>
            <?php }
            } else {
                if($faqs) { 
                    foreach($faqs as $faq) { ?>
                        <details open>
                            <summary><?=$faq->question?></summary>
                            <?=html_entity_decode($faq->answer)?>
                        </details>
        <?php       }
                }
            }
        ?>
		</div>
    </div>
</main>