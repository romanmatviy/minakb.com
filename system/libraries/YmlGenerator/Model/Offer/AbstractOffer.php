<?php

/*
 * This file is part of the Bukashk0zzzYmlGenerator
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\YmlGenerator\Model\Offer;

/**
 * Abstract Class Offer
 */
abstract class AbstractOffer implements OfferInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $available;

    /**
     * @var string
     */
    private $sellingType;

    /**
     * @var string
     */
    private $url;

    /**
     * @var float
     */
    private $price;
    private $prices = [];

    /**
     * @var float
     */
    private $oldPrice;

    /**
     * @var string
     */
    private $currencyId;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var string
     */
    private $marketCategory;

    /**
     * @var bool
     */
    private $adult;

    /**
     * @var string
     */
    private $salesNotes;

    /**
     * @var bool
     */
    private $manufacturerWarranty;

    /**
     * @var bool
     */
    private $pickup;

    /**
     * @var bool
     */
    private $downloadable;

    /**
     * @var bool
     */
    private $delivery;

    /**
     * @var float
     */
    private $localDeliveryCost;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $quantity_in_stock = -1;

    /**
     * @var string
     */
    private $countryOfOrigin;

    /**
     * @var array
     */
    private $pictures = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @return array
     */
    public function toArray()
    {
        return \array_merge($this->getHeaderOptions(), $this->getOptions(), $this->getFooterOptions());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * @param bool $available
     *
     * @return $this
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return string
     */
    public function getSellingType()
    {
        return $this->sellingType;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setSellingType($sellingType)
    {
        $this->sellingType = $sellingType;

        return $this;
    }
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getOldPrice()
    {
        return $this->oldPrice;
    }

    /**
     * @param float $oldPrice
     *
     * @return $this
     */
    public function setOldPrice($oldPrice)
    {
        $this->oldPrice = $oldPrice;

        return $this;
    }

    /**
     * @return array[$from => $price]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param array[int $from => float $price]
     *
     * @return $this
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    /**
     * @param string $currencyId
     *
     * @return $this
     */
    public function setCurrencyId($currencyId)
    {
        $this->currencyId = $currencyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     *
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMarketCategory()
    {
        return $this->marketCategory;
    }

    /**
     * @param string $marketCategory
     *
     * @return $this
     */
    public function setMarketCategory($marketCategory)
    {
        $this->marketCategory = $marketCategory;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdult()
    {
        return $this->adult;
    }

    /**
     * @param bool $adult
     *
     * @return $this
     */
    public function setAdult($adult)
    {
        $this->adult = $adult;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalesNotes()
    {
        return $this->salesNotes;
    }

    /**
     * @param string $salesNotes
     *
     * @return $this
     */
    public function setSalesNotes($salesNotes)
    {
        $this->salesNotes = $salesNotes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isManufacturerWarranty()
    {
        return $this->manufacturerWarranty;
    }

    /**
     * @param bool $manufacturerWarranty
     *
     * @return $this
     */
    public function setManufacturerWarranty($manufacturerWarranty)
    {
        $this->manufacturerWarranty = $manufacturerWarranty;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPickup()
    {
        return $this->pickup;
    }

    /**
     * @param bool $pickup
     *
     * @return $this
     */
    public function setPickup($pickup)
    {
        $this->pickup = $pickup;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * @param bool $downloadable
     *
     * @return $this
     */
    public function setDownloadable($downloadable)
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param bool $delivery
     *
     * @return $this
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * @return float
     */
    public function getLocalDeliveryCost()
    {
        return $this->localDeliveryCost;
    }

    /**
     * @param float $localDeliveryCost
     *
     * @return $this
     */
    public function setLocalDeliveryCost($localDeliveryCost)
    {
        $this->localDeliveryCost = $localDeliveryCost;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity_in_stock()
    {
        return $this->quantity_in_stock;
    }

    /**
     * @param int $quantity_in_stock
     *
     * @return $this
     */
    public function setQuantity_in_stock($quantity_in_stock)
    {
        $this->quantity_in_stock = $quantity_in_stock;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        if(empty(trim($description)))
            $this->description = '';
        else
            $this->description = '<![CDATA['.$description.']]>';

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfOrigin()
    {
        return $this->countryOfOrigin;
    }

    /**
     * @param string $countryOfOrigin
     *
     * @return $this
     */
    public function setCountryOfOrigin($countryOfOrigin)
    {
        $this->countryOfOrigin = $countryOfOrigin;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param OfferParam $param
     *
     * @return $this
     */
    public function addParam(OfferParam $param)
    {
        $this->params[] = $param;

        return $this;
    }

    /**
     * Add picture
     *
     * @param string $url
     *
     * @return $this
     */
    public function addPicture($url)
    {
        if (\count($this->pictures) < 10) {
            $this->pictures[] = $url;
        }

        return $this;
    }

    /**
     * Set pictures
     *
     * @param array $pictures
     *
     * @return $this
     */
    public function setPictures(array $pictures)
    {
        $this->pictures = $pictures;

        return $this;
    }

    /**
     * Get picture list
     *
     * @return array
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @return array
     */
    abstract protected function getOptions();

    /**
     * @return array
     */
    private function getHeaderOptions()
    {
        $quantity_in_stock = $this->getQuantity_in_stock();
        if($quantity_in_stock >= 0)
            return [
                'url' => $this->getUrl(),
                'price' => $this->getPrice(),
                'prices' => $this->getPrices(),
                'quantity_in_stock' => $quantity_in_stock,
                'oldprice' => $this->getOldPrice(),
                'currencyId' => $this->getCurrencyId(),
                'categoryId' => $this->getCategoryId(),
                'market_category' => $this->getMarketCategory(),
                'picture' => $this->getPictures(),
                'pickup' => $this->isPickup(),
                'delivery' => $this->isDelivery(),
                'local_delivery_cost' => $this->getLocalDeliveryCost(),
            ];
        else
            return [
                'url' => $this->getUrl(),
                'price' => $this->getPrice(),
                'prices' => $this->getPrices(),
                'oldprice' => $this->getOldPrice(),
                'currencyId' => $this->getCurrencyId(),
                'categoryId' => $this->getCategoryId(),
                'market_category' => $this->getMarketCategory(),
                'picture' => $this->getPictures(),
                'pickup' => $this->isPickup(),
                'delivery' => $this->isDelivery(),
                'local_delivery_cost' => $this->getLocalDeliveryCost(),
            ];
    }

    /**
     * @return array
     */
    private function getFooterOptions()
    {
        return [
            'description' => $this->getDescription(),
            'sales_notes' => $this->getSalesNotes(),
            'manufacturer_warranty' => $this->isManufacturerWarranty(),
            'country_of_origin' => $this->getCountryOfOrigin(),
            'downloadable' => $this->isDownloadable(),
            'adult' => $this->isAdult(),
        ];
    }
}
