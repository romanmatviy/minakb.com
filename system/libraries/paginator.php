<?php

/*
 * Шлях: SYS_PATH/libraries/paginator.php
 *
 * Відображення блоку з переключенням сторінок.
 * Версія 1.0 (08.10.2015) - основа бібліотеки. Функції public: paginate(), style(), get(); private: make().
 * Використовуються як вхідні дані по замовчуванню: 
 *     $_SESSION['option']->paginator_total - загальна кількість сторінок у розділі
 *     $_SESSION['option']->paginator_per_page - кількість сторінок на сторінці
 *     $_GET['page'] - поточна сторінка
 * Версія 1.1 (12.10.2015) - додано можливість задання стилів по замовчуванню з конфігураційного файлу
 * Версія 1.1.1 (26.07.2016) - адаптовано до php7
 * Версія 1.1.2 (24.11.2016) - виправлено помилку зайвої сторінки
 */

class Paginator {

    private $per_page = 10;
    private $total = 0;
    private $current_page = -1;
    public $ul_id = '';
    public $ul_class = 'pagination';
    public $li_class = '';
    public $li_class_active = 'active';
    public $li_previous_text = '<';
    public $li_previous_class_active = 'previous';
    public $li_previous_class_non_active = 'previous-off';
    public $li_next_text = '>';
    public $li_next_class_active = 'next';
    public $li_next_class_non_active = 'next-off';
    public $li_a_class = '';
    public $li_a_class_active = '';

    /*
     * Отримуємо дані для стилю по замовчуванню з конфігураційного файлу
     * Формат [ключ] = [клас по замовчуванню|активний клас (згідно style())]
     */
    function __construct($cfg = array())
    {
        if(!empty($cfg)){
            foreach ($cfg as $element => $class) {
                $class = explode('|', $class);
                $class_active = 'active';
                if(isset($class[1])) {
                    $class_active = $class[1];
                }
                $class = $class[0];
                $this->style($element, $class, $class_active);
            }
        }
    }

    public function get()
    {
        if($this->current_page < 0) {
            $this->paginate();
        }
        return $this->make();
    }

    public function paginate($current = 'auto', $total = 0, $per_page = 10)
    {
        $this->current_page = 1;
        if($current == 'auto' && isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0){
            $this->current_page = $_GET['page'];
        } elseif(is_numeric($current) && $current > 0){
            $this->current_page = $current;
        }
        
        if(isset($_SESSION['option']->paginator_total) && $_SESSION['option']->paginator_total > 0){
            $this->total = $_SESSION['option']->paginator_total;
        } else {
            $this->total = $total;
        }

        if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0){
            $this->per_page = $_SESSION['option']->paginator_per_page;
        } else {
            $this->per_page = $per_page;
        }
    }

    public function style($element = false, $class = '', $class_active = 'active')
    {
        switch ($element) {
            case 'ul':
                $this->ul_class = $class;
                break;
            case 'li':
                $this->li_class = $class;
                $this->li_class_active = $class_active;
                break;
            case 'previous':
            case '-':
                $this->li_previous_class_active = $class;
                $this->li_previous_class_non_active = $class_active;
                break;
            case 'previous text':
            case '- text':
                $this->li_previous_text = $class;
                break;
            case 'next':
            case '+':
                $this->li_next_class_active = $class;
                $this->li_next_class_non_active = $class_active;
                break;
            case 'next text':
            case '+ text':
                $this->li_next_text = $class;
                break;
            case 'a':
            case 'li a':
            case 'ul li a':
                $this->li_a_class = $class;
                $this->li_a_class_active = $class_active;
                break;
        }
    }

    private function make()
    {
        $list = '';
        if($this->total > $this->per_page){
            $pages = ceil($this->total / $this->per_page);

            $start = 1;
            $finish = $pages;
            if($pages > 5) {
                $finish = 5;
                $start = $this->current_page - 2;
                $finish = $this->current_page + 2;
                if($finish > $pages) $finish = $pages;
                if($start < 1) $start = 1;
            }

            if($pages > 1){
                $link = SERVER_URL . $_GET['request'];
                $link .= '?';
                foreach ($_GET as $key => $value) {
                    if($key != 'request' && $key != 'page'){
                        if(!is_array($value)) {
                            $link .= $key .'='.$value . '&';
                        } else {
                            foreach ($value as $key2 => $value2) {
                                $link .= $key .'%5B%5D='.$value2 . '&';
                            }
                        }
                    }
                }
                $link_1 = substr($link, 0, -1);
                $link .= 'page=';

                $list = "<ul id=\"{$this->ul_id}\" class=\"{$this->ul_class}\">";

                if($this->current_page > 1) {
                    $list .= '<li class="'.$this->li_previous_class_active.'">';
                    $list .= '<a href="';
                    if($this->current_page - 1 > 1) {
                        $list .= $link . ($this->current_page - 1);
                    } else {
                        $list .= $link_1;
                    } 
                    $list .= '" class="'.$this->li_a_class_active.'">'.$this->li_previous_text.'</a>';
                    $list .= '</li>';
                } elseif($this->li_previous_class_non_active != ''){
                    $list .= '<li class="'.$this->li_previous_class_non_active.'"><span>'.$this->li_previous_text.'</span></li>';
                }

                $list .= '<li class="';
                $list .= ($this->current_page == 1) ? $this->li_class_active : $this->li_class;
                $list .= '">';
                if($start == 1) { 
                    $start++; 
                    
                    if($this->current_page > 1) {
                        $list .= '<a href="' . $link_1 . '"> 1 </a>';
                    } else {
                        $list .= '<span> 1 </span>';
                    }
                } else {
                    $list .= '<a href="' . $link_1 . '"> 1 </a>';
                }
                $list .= '</li>';

                for($page = $start; $page <= $finish; $page++) {
                    $list .= '<li class="';

                    if($this->current_page == $page) {
                        $list .= $this->li_class_active . '"><span>' . $page . '</span>';
                    } else {
                        $list .= $this->li_class . '">';
                        $list .= '<a href="' . $link . $page . '">' . $page . '</a>';
                    }

                    $list .= '</li>';
                }

                if($page < $pages) {
                    $list .= '<li class="' . $this->li_class . '">';
                    $list .= '<a href="' . $link . $pages . '">' . $pages . '</a>';
                    $list .= '</li>';
                }

                if($this->li_next_class_non_active != ''){
                    $next = $this->current_page + 1;

                    $list .= '<li class="';
                    $list .= ($next <= $pages) ? $this->li_next_class_active : $this->li_next_class_non_active;
                    $list .= '">';
                    
                    if($next <= $pages) { 
                        $list .= '<a href="' . $link . $next .'">' . $this->li_next_text . '</a>';
                    } else {
                        $list .= '<span>' . $this->li_next_text . '</span>';
                    }

                    $list .= '</li>';
                }

                $list .= '</ul>';
            }
        }
        return $list;
    }
}

?>