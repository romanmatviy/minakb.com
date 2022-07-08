<div class="row">
	<div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL?>admin/cart/<?= $cart->id?>" class="btn btn-success btn-xs"><i class="fa fa-pencil-square-o"></i> Керувати замовленням</a>
					<a href="<?=SITE_URL?>admin/cart/<?= $cart->id?>/print?go" class="btn btn-danger btn-xs" target="_blank"><i class="fa fa-print"></i> Друкувати</a>
                </div>
                <h4 class="panel-title">Попередній перегляд до друку Замовлення #<?= $cart->id?> від <?= date('d.m.Y H:i', $cart->date_edit)?></h4>
            </div>
			<div class="panel-body">
    		<div class="clearfix">
    			<?php require APP_PATH.'services/'.$_SESSION['service']->name. '/views/__print_body.php'; ?>
    		</div>
    	</div>
    </div>
</div>