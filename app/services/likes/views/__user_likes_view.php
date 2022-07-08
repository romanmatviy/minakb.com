<h1><?=$_SESSION['alias']->name?></h1>

<div class="row">
    
    <?php if($likes_list) foreach ($likes_list as $like) { ?>
        <div class="col-md-4">
            <?php if($page = $this->load->function_in_alias($like->alias, '__get_Search', $like->content)) {
                if(!empty($page->folder) && $image = $this->wl_search_model->getImage($like->alias, $like->content, $page->folder, 'm_'))
                {
                    $path = substr(IMG_PATH, strlen(SITE_URL));
                    if(file_exists($path.$image))
                    {
                        echo('<a href="'.SITE_URL.$page->link.'" target="_blank">');
                        echo '<img class="full-width img-responsive" src="'.IMG_PATH.$image.'" alt="'.$like->page_name.'" title="'.$like->page_name.'">';
                        echo '</a>';
                    }
                }
                echo('<a href="'.SITE_URL.$page->link.'" target="_blank">'.$like->page_name.'</a>');
            }
            else
                echo $like->page_name;
                ?>
        </div>
    <?php } ?>
        
</div>

<?php
$this->load->library('paginator');
echo $this->paginator->get();
?>