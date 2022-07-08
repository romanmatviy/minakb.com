<div class="row">
	<div class="col-md-12 search-row">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/search">
            <div class="col-sm-2 search-col">
                <input type="text" name="<?=($_SESSION['option']->ProductUseArticle) ? 'article' : 'id'?>" class="form-control" placeholder="<?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'ID'?> *весь магазин" value="<?=$this->data->get('article')?>">
            </div>
            <?php if(!empty($group)) { ?>
	            <div class="col-sm-5 search-col">
	                <input type="text" name="name" class="form-control" placeholder="Назва товару" value="<?=$this->data->get('name')?>">
	            </div>
	            <div class="col-sm-2 search-col">
	                <select name="group" class="form-control">
	                	<option value="<?=$group->id?>">У групі/підгрупах <?=$_SESSION['alias']->name?></option>
	                	<option value="0">По цілому магазину</option>
	                </select>
	            </div>
	            <div class="col-lg-3 col-sm-4 search-col">
	                <button class="btn btn-primary btn-search btn-block"><i class="fa fa-search"></i><strong> Знайти</strong></button>
	            </div>
            <?php } else { ?>
                <div class="col-sm-6 search-col">
	                <input type="text" name="name" class="form-control" placeholder="Назва товару" value="<?=$this->data->get('name')?>">
	            </div>
	            <div class="col-lg-4 col-sm-4 search-col">
	                <button class="btn btn-primary btn-search btn-block"><i class="fa fa-search"></i><strong> Знайти</strong></button>
	            </div>
            <?php } ?>
        </form>
        <div class="clear"></div>
    </div>
</div>

<style type="text/css">
	.search-row {
	    max-width: 800px;
	    margin-left: auto;
	    margin-right: auto;
	    float: none;
	    margin-bottom: 20px;
	}
	.search-row .search-col {
	    padding: 0;
	    position: relative;
	}
	.search-row .search-col .form-control {
	    border: 1px solid #16A085;
	    border-radius: 0;
	}
	.search-row .search-col:first-child .form-control {
	    border-radius: 3px 0 0 3px;
	}
</style>