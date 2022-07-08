<div class="add-your-review w40-5 m-w100">
    <?php if(!empty($_SESSION['notify']->errors)) { ?>
       <div id="comment_add_error" class="alert alert-danger">
            <span class="close" data-dismiss="alert">×</span>
            <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Error!'?></h4>
            <p><?=$_SESSION['notify']->errors?></p>
        </div>
    <?php } unset($_SESSION['notify']); ?>

    <h3><?=$this->text('Додати відгук')?></h3>
    <form action="<?=SITE_URL?>comments/add" method="POST" class="review-form" enctype="multipart/form-data">
        <input type="hidden" name="content" value="<?= $content?>">
        <input type="hidden" name="alias" value="<?= $alias?>">
        <input type="hidden" name="image_name" value="<?= $image_name?>">

        <div class="d-flex">
            <div class="rating d-flex v-center">
                <span><?=$this->text('Оцінка')?></span>
                <div class="d-iblock">
                    <label <?=$this->data->re_post('rating') == 5 ? 'class="checked"':''?>><input type="radio" name="rating" value="5" <?=$this->data->re_post('rating') == 5 ? 'selected':''?>></label>
                    <label <?=$this->data->re_post('rating') == 5 ? 'class="checked"':''?>><input type="radio" name="rating" value="4" <?=$this->data->re_post('rating') == 5 ? 'selected':''?>></label>
                    <label <?=$this->data->re_post('rating') == 5 ? 'class="checked"':''?>><input type="radio" name="rating" value="3" <?=$this->data->re_post('rating') == 5 ? 'selected':''?>></label>
                    <label <?=$this->data->re_post('rating') == 5 ? 'class="checked"':''?>><input type="radio" name="rating" value="2" <?=$this->data->re_post('rating') == 5 ? 'selected':''?>></label>
                    <label <?=$this->data->re_post('rating') == 5 ? 'class="checked"':''?>><input type="radio" name="rating" value="1" <?=$this->data->re_post('rating') == 5 ? 'selected':''?>></label>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div>
                <label class="image-review-style" for="image-review"><?=$this->text('Додати зображення')?> <i class="fas fa-download" aria-hidden="true"></i></label>
                <input type="file" name="images[]" accept="image/jpg,image/jpeg,image/png" multiple id="image-review">
            </div>
        </div>
        <div class="review-gallery"></div>
        <textarea name="comment" id="review-text" rows="6" placeholder="<?=$this->text('Відгук')?>" required><?=$this->data->re_post('comment')?></textarea>
        <?php if($this->userIs()) { ?>
            <button><?=$this->text('Додати відгук')?></button>
        <?php } else { ?>
            <div class="d-flex h-center">
                <?php $this->load->library('recaptcha');
                    $this->recaptcha->form('recaptchaVerifyCallback', 'recaptchaExpiredCallback'); ?>
            </div>
            <div class="d-flex">
                <div class="w50-5 m-w100">
                    <input type="text" name="name" placeholder="<?=$this->text("Ім'я")?>*" value="<?=$this->data->re_post('name')?>" required>
                </div>
                <div class="w50-5 m-w100">
                    <input type="email" name="email" placeholder="Email*" value="<?=$this->data->re_post('email')?>" required>
                </div>
            </div>
            <button class="review-btn" title='<?=$this->text('Заповніть "Я не робот"')?>' disabled><?=$this->text('Додати відгук')?></button>
        <?php } ?>
    </form>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('.rating label').click(function() {
            $('.rating label').removeClass('checked')
            $(this).addClass('checked');
        });

        $("#image-review").change(function() {
            $(this).prev().css({
                color: '#b59759',
                'background-color': '#fff'
            }).html('<?=$this->text('Change image')?> <i class="fa fa-download" aria-hidden="true"></i>');
            imagesPreview(this, 'div.review-gallery');
        });

        var imagesPreview = function(input, placeToInsertImagePreview) {
            $(placeToInsertImagePreview).empty();
            if (input.files) {
                var filesAmount = input.files.length;
                for (i = 0; i < filesAmount; i++) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        $($.parseHTML('<img>')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                    }
                    reader.readAsDataURL(input.files[i]);
                }
            }
        };
    }
    var recaptchaVerifyCallback = function(response) {
        $('.add-your-review .review-btn').attr('disabled', false);
        $('.add-your-review .review-btn').attr('title', false);
    };
    var recaptchaExpiredCallback = function(response) {
        $('.add-your-review .review-btn').attr('disabled', true);
        $('.add-your-review .review-btn').attr('title', '<?=$this->text('Заповніть "Я не робот"')?>');
    };
</script>