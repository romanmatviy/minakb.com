<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title"><?=$_SESSION['alias']->name?>. <?=$searchForm ? 'Товари до підтвердження':'Товари автора'?></h4>
    </div>
    <div class="panel-body">
        <?php if($searchForm)
            require '__search_subview.php';

        if($products) {
            $_SESSION['option']->productOrder = 'status';
            require 'products/__products-list.php';
        } else { ?>
            <div class="alert alert-info">
                <h4>Товари відсутні</h4>
            </div>
        <?php } ?>
    </div>
</div>