<?php

include 'YmlGenerator/Model/Offer/OfferGroupTrait.php';
include 'YmlGenerator/Model/Offer/OfferGroupAwareInterface.php';
include 'YmlGenerator/Model/Offer/OfferInterface.php';
include 'YmlGenerator/Model/Offer/AbstractOffer.php';
include 'YmlGenerator/Model/Offer/OfferSimple.php';
include 'YmlGenerator/Model/Offer/OfferParam.php';
include 'YmlGenerator/Model/Category.php';
include 'YmlGenerator/Model/Currency.php';
include 'YmlGenerator/Model/Delivery.php';
include 'YmlGenerator/Model/ShopInfo.php';
include 'YmlGenerator/Settings.php';
include 'YmlGenerator/Generator.php';

use Bukashk0zzz\YmlGenerator\Model\Offer\OfferSimple;
use Bukashk0zzz\YmlGenerator\Model\Offer\OfferParam;
use Bukashk0zzz\YmlGenerator\Model\Category;
use Bukashk0zzz\YmlGenerator\Model\Currency;
use Bukashk0zzz\YmlGenerator\Model\Delivery;
use Bukashk0zzz\YmlGenerator\Model\ShopInfo;
use Bukashk0zzz\YmlGenerator\Settings;
use Bukashk0zzz\YmlGenerator\Generator;

class ymlgenerator extends Controller {

    // protected $encoding = 'windows-1251';
    protected $encoding = 'utf-8';
    private $groupsInList = array();

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public function getStringWithEncoding($string='')
    {
        if($this->encoding == 'utf8' || $this->encoding == 'utf-8')
            return $string;
        return mb_convert_encoding($string, $this->encoding);
    }

    public function createYml($products, $groups)
    {
        set_time_limit(0);

        $currencies = $categories = array();

        $file = 'php://output';
        // $file = 'export.xml';
        $settings = (new Settings())->setOutputFile($file)->setEncoding($this->encoding);

        $shopInfo = (new ShopInfo())
            
            ->setName(SITE_NAME)
            ->setUrl(SITE_URL);

        $currencies[] = (new Currency())
            ->setId('UAH')
            ->setRate(1);
        // $currencies[] = (new Currency())
        //     ->setId('USD')
        //     ->setRate(30);

        if($groups)
            foreach ($groups as $group) 
            {
                if(!in_array($group->id, $this->groupsInList))
                {
                    if(!empty($group->prom_id))
                        $categories[] = (new Category())
                            ->setId($group->id)
                            ->setParentId($group->parent)
                            ->setPortalId($group->prom_id)
                            ->setName($group->name);
                    else
                        $categories[] = (new Category())
                            ->setId($group->id)
                            ->setParentId($group->parent)
                            ->setName($group->name);
                    $this->groupsInList[] = $group->id;
                }
            }

        $offers = [];
        if(!empty($products))
        foreach ($products as $product) 
        {
            if(!empty($product->group) && is_array($product->group))
                foreach ($product->group as $group)
                {
                    $productId = $product->id .'-'.$group->id;

                    $offerSimple = (new OfferSimple())
                        ->setId($productId)
                        ->setSellingType('u')
                        ->setUrl(SITE_URL.$product->link)
                        ->setPrice($product->price)
                        ->setCurrencyId('UAH')
                        ->setDelivery(true)
                        ->setName($product->name)
                        ->setPictures($product->images)
                        ->setVendorCode($product->article_show)
                        ->setCategoryId($group->id)
                        ->setDescription($product->text);
                    if(!empty($product->vendor))
                        $offerSimple->setVendor($product->vendor);

                    if($product->old_price > 0 && $product->old_price > $product->price)
                        $offerSimple->setOldPrice($product->old_price);

                    if($_SESSION['option']->useAvailability)
                    {
                        if($product->availability > 0)
                            $offerSimple->setAvailable(true);
                        else
                            $offerSimple->setAvailable(NULL);
                        $offerSimple->setQuantity_in_stock($product->availability);
                    }
                    else
                    {
                        if($product->availability == 1)
                            $offerSimple->setAvailable(true);
                        else if($product->availability == 2)
                            $offerSimple->setAvailable(false);
                        else
                            $offerSimple->setAvailable(NULL);
                    }

                    if(isset($product->quantity_in_stock))
                        $offerSimple->setQuantity_in_stock($product->quantity_in_stock);

                    if(!empty($product->options))
                        foreach ($product->options as $name => $value)
                        {
                            $offerSimple->addParam((new OfferParam())
                                ->setName($name)
                                ->setValue($value));
                        }

                    if(!empty($product->prices) && is_array($product->prices))
                        $offerSimple->addParam((new OfferParam())
                                ->setName('prices')
                                ->setValue($product->prices));

                    $offerSimple->addParam((new OfferParam())
                                ->setName('Состояние')
                                ->setValue('Новое'));

                    $offers[] = $offerSimple;
                }
            else
            {
                $offerSimple = (new OfferSimple())
                    ->setId($product->id)
                    ->setSellingType('u')
                    ->setUrl(SITE_URL.$product->link)
                    ->setPrice($product->price)
                    ->setCurrencyId('UAH')
                    ->setDelivery(true)
                    ->setName($product->name)
                    ->setPictures($product->images)
                    ->setVendorCode($product->article_show)
                    ->setDescription($product->text);
                if(!empty($product->group))
                    $offerSimple->setCategoryId($product->group);
                if($product->old_price > 0 && $product->old_price > $product->price)
                    $offerSimple->setOldPrice($product->old_price);
                if(!empty($product->vendor))
                    $offerSimple->setVendor($product->vendor);

                if($_SESSION['option']->useAvailability)
                {
                    if($product->availability > 0)
                        $offerSimple->setAvailable(true);
                    else
                        $offerSimple->setAvailable(NULL);
                    $offerSimple->setQuantity_in_stock($product->availability);
                }
                else
                {
                    if($product->availability == 1)
                        $offerSimple->setAvailable(true);
                    else if($product->availability == 2)
                        $offerSimple->setAvailable(false);
                    else
                        $offerSimple->setAvailable(NULL);
                }

                if(isset($product->quantity_in_stock))
                    $offerSimple->setQuantity_in_stock($product->quantity_in_stock);

                if(!empty($product->options))
                    foreach ($product->options as $name => $value)
                    {
                        $offerSimple->addParam((new OfferParam())
                            ->setName($name)
                            ->setValue($value));
                    }

                if(!empty($product->prices) && is_array($product->prices))
                    $offerSimple->setPrices($product->prices);

                $offerSimple->addParam((new OfferParam())
                            ->setName('Состояние')
                            ->setValue('Новое'));

                $offers[] = $offerSimple;
            }
        }

        header("Content-Type: text/xml; charset={$this->encoding}");

        (new Generator($settings))->generate(
            $shopInfo,
            $currencies,
            $categories,
            $offers
        );

        // $this->showTime();
        exit;
    }

    private function showTime()
    {
        $mem_end = memory_get_usage();
        $time_end = microtime(true);
        $time = $time_end - $GLOBALS['time_start'];
        $mem = $mem_end - $GLOBALS['mem_start'];
        $mem = round($mem/1024, 5);
        if($mem > 1024)
        {
            $mem = round($mem/1024, 5);
            $mem = (string) $mem . ' Мб';
        }
        else
            $mem = (string) $mem . ' Кб';

        $after = ($_SESSION['cache']) ? 'Cache активний' : 'Cache відключено';
        echo '<hr><center>Час виконання: '.round($time, 5).' сек. Використанок памяті: '.$mem.'. Запитів до БД: '.$this->db->count_db_queries.'. '.$after.'</center>';
    }
}
?>