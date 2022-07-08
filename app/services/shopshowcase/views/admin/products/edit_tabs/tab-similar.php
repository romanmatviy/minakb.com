<?php if(!empty($_SESSION['option']->similarFolders)) {
        if(!is_array($_SESSION['option']->similarFolders))
            $_SESSION['option']->similarFolders = unserialize($_SESSION['option']->similarFolders);
    }
if($_SESSION['user']->admin) { ?>
<form id="similarFolders" class="row" method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/similarFolders" style="display: none;">
    <h4>Групи подібності</h4>
    <div class="col-sm-6">
        <table class="table">
            <?php if(!empty($_SESSION['option']->similarFolders)) {
                foreach($_SESSION['option']->similarFolders as $key => $name) { ?>
                    <tr>
                        <td><input type="text" name="key-<?=$key?>" class="form-control" value="<?=$key?>" placeholder="key (анг)" maxlength="30"></td>
                        <td><input type="text" name="name-<?=$key?>" class="form-control" value="<?=$name?>" placeholder="Назва"></td>
                    </tr>
                <?php }
             } ?>
             <tr>
                <td><input type="text" name="key-new" class="form-control" placeholder="key (анг)" maxlength="30"></td>
                <td><input type="text" name="name-new" class="form-control" placeholder="Назва"></td>
            </tr>
            <tr>
                <td colspan="2" class="text-center">
                    <button class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>
                </td>
            </tr>
        </table>
    </div>
</form>
<?php } ?>
<form class="row form-inline" method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/addSimilarProduct" >
    <?php if($_SESSION['user']->admin) { ?>
    <button type="button" class="btn btn-info pull-right" title="Групи подібності" onclick="$('#similarFolders').slideToggle()"><i class="fa fa-cog"></i></button>
    <?php } ?>
    <input type="hidden" name="product" value="<?= $product->id ?>">
    <div class="form-group m-r-10">
        <label class="control-label"><?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'Id'?> подібного товару</label>
    </div>
    <div class="form-group m-r-10">
        <input type="<?=($_SESSION['option']->ProductUseArticle) ? 'text' : 'number'?>" class="form-control" name="article" value="" placeholder="<?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'Id'?>" required>
    </div>
    <?php if(!empty($_SESSION['option']->similarFolders)) { ?>
    <div class="form-group m-r-10">
        <select name="folder" class="form-control" title="Групи подібності" required>
            <?php foreach($_SESSION['option']->similarFolders as $key => $name) {
                echo "<option value='{$key}'>{$name}</option>";
            } ?>
        </select>
    </div>
    <?php } ?>
    <div class="form-group m-r-10">
        <button class="btn btn-warning"><i class="fa fa-plus" aria-hidden="true"></i> Додати</button>
    </div>
</form>
<?php if(!empty($_SESSION['option']->similarFolders)) { 
    foreach($_SESSION['option']->similarFolders as $similarFolderKey => $similarFolderName) { ?>
    <div class="row m-t-15">
        <h4><?=$similarFolderName?></h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered nowrap" width="100%">
                <thead>
                    <tr>
                        <th><?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'Id'?></th>
                        <th>Назва</th>
                        <th>Ціна (у.о.)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($product->similarProducts[$similarFolderKey])) foreach($product->similarProducts[$similarFolderKey] as $similarProduct) { ?>
                    <tr>
                        <td><a href="<?=SITE_URL.'admin/'.$similarProduct->link?>"><?=($_SESSION['option']->ProductUseArticle) ? $similarProduct->article_show : $similarProduct->id?></a></td>
                        <td><a href="<?=SITE_URL.'admin/'.$similarProduct->link?>"><?= $similarProduct->name ?></a></td>
                        <td><?= $similarProduct->price.' '.$similarProduct->currency ?></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="deleteSimilarProduct(<?= $similarProduct->similar_id?>, this);" ><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } } else { ?>
<div class="row m-t-15">
    <div class="table-responsive">
        <table class="table table-striped table-bordered nowrap" width="100%">
            <thead>
                <tr>
                    <th><?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'Id'?></th>
                    <th>Назва</th>
                    <th>Ціна (у.о.)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($product->similarProducts as $folderName => $similarProductOrFolder) {
                if(is_array($similarProductOrFolder)) {
                    if($folderName != 'default') {
                        echo "<tr><td colspan='4'>{$folderName}:</td></tr>";
                    }
                    foreach($similarProductOrFolder as $similarProduct) {
                        $link = SITE_URL . "admin/{$similarProduct->link}"; ?>
                        <tr>
                            <td><a href="<?=SITE_URL.'admin/'.$similarProduct->link?>"><?=($_SESSION['option']->ProductUseArticle) ? $similarProduct->article_show : $similarProduct->id?></a></td>
                            <td><a href="<?=SITE_URL.'admin/'.$similarProduct->link?>"><?= $similarProduct->name ?></a></td>
                            <td><?= $similarProduct->price.' '.$similarProduct->currency ?></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="deleteSimilarProduct(<?= $similarProduct->id?>, this);" >X</button></td>
                        </tr>
                    <?php }
                }
                else if(is_object($similarProductOrFolder)) {
                    $link = SITE_URL . "admin/{$similarProductOrFolder->link}"; ?>
                    <tr>
                        <td><a href="<?=SITE_URL.'admin/'.$similarProductOrFolder->link?>"><?=($_SESSION['option']->ProductUseArticle) ? $similarProductOrFolder->article_show : $similarProductOrFolder->id?></a></td>
                        <td><a href="<?=SITE_URL.'admin/'.$similarProductOrFolder->link?>"><?= $similarProductOrFolder->name ?></a></td>
                        <td><?= $similarProductOrFolder->price.' '.$similarProductOrFolder->currency ?></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="deleteSimilarProduct(<?= $similarProductOrFolder->id?>, this);" >X</button></td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
    </div>
</div>
<?php }


if($product->similarProducts && false) {?>
<div class="col-md-12">
    <h4 class="text-center">Опис для всіх схожих продуктів</h4>
    <?php if($_SESSION['language']){ ?>
        <ul class="nav nav-tabs">
            <?php foreach ($_SESSION['all_languages'] as $lang) { ?>
                <li class="<?=($_SESSION['language'] == $lang) ? 'active' : ''?>"><a href="#language-tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
            <?php } ?>
        </ul>
        <div class="tab-content">
            <?php foreach ($_SESSION['all_languages'] as $lang) { ?>
            <div class="tab-pane fade <?=($_SESSION['language'] == $lang) ? 'active in' : ''?>" id="language-tab-<?=$lang?>">
                <form class="form-vertical" method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/saveSimilarText" >
                    <input type="hidden" name="language" value="<?= $lang?>">
                    <input type="hidden" name="group" value="<?= end($product->similarProducts)->group ?>">
                    <div class="form-group">
                        <textarea class="t-big" name="text" id="editorSimilar-<?=$lang?>"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="all" name="all" value="1" checked> Перезаписувати існуючий текст?
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-success" value="Зберегти">
                    </div>
                </form>
            </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <form class="form-vertical" method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/saveSimilarText" >
            <input type="hidden" name="group" value="<?= end($product->similarProducts)->group ?>">
            <div class="form-group">
                <textarea class="t-big" name="text" id="editorSimilar"></textarea>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="all" name="all" value="1" checked>  Перезаписувати існуючий текст?
                    </label>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-success" value="Зберегти">
            </div>
        </form>
    <?php } ?>
</div>
<?php } ?>

<?php if (false): ?>
<script type="text/javascript" src="<?=SITE_URL?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>assets/ckfinder/ckfinder.js"></script>
<script type="text/javascript">
    <?php if($_SESSION['language']) foreach($_SESSION['all_languages'] as $lng){ echo "CKEDITOR.replace( 'editorSimilar-{$lng}' ); ";} else echo "CKEDITOR.replace( 'editorSimilar' ); "; ?>
        CKFinder.setupCKEditor( null, {
        basePath : '<?=SITE_URL?>assets/ckfinder/',
        filebrowserBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html?type=Images',
        filebrowserFlashBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html?type=Flash',
        filebrowserUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Images',
        filebrowserFlashUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Flash',
    });
</script>
<?php endif ?>
<script>
    function deleteSimilarProduct(id, btn) {
        if(confirm("Ви впевнені, що хочете видалити товар зі схожих продуктів?"))
        {
            $.ajax({
                url: "<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/deleteSimilarProduct",
                type: "POST",
                data: {
                    id : id
                },
                success : function (res) 
                {
                    $(btn).closest('tr').hide('slow', function(){ $(this).remove(); });
                }
            })
        }
    }
</script>
