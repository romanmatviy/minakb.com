<?php namespace Zenwalker\CommerceML\Model;

use Zenwalker\CommerceML\ORM\Model;

class Product extends Model
{
    /**
     * @var string $id
     */
    public $id;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @var string $sku
     */
    public $sku;

    public $box;
    public $weight;

    /**
     * @var string $unit
     */
    public $unit;
    public $termin;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var int $quantity
     */
    public $quantity;
    public $storage_1 = 0;
    public $storage_2 = 0;

    /**
     * @var array $price
     */
    public $price = [];

    /**
     * @var array $categories
     */
    public $categories = [];

    /**
     * @var array $requisites
     */
    public $requisites = [];

    /**
     * @var array $properties
     */
    public $properties = [];
    public $properties_ru = [];

    /**
     * Class constructor.
     *
     * @param string [$importXml]
     * @param string [$offersXml]
     */
    public function __construct($importXml = null, $offersXml = null)
    {
        $this->name        = '';
        $this->quantity    = 0;
        $this->description = '';

        if (!is_null($importXml)) {
            $this->loadImport($importXml);
        }

        if (!is_null($offersXml)) {
            $this->loadOffers($offersXml);
        }
    }

    /**
     * Load primary data from import.xml.
     *
     * @param \SimpleXMLElement $xml
     *
     * @return void
     */
    public function loadImport($xml)
    {
        $this->id = trim($xml->Ид);

        $this->name        = trim($xml->Наименование);
        $this->name_ru        = trim($xml->Наименование_ru);
        $this->description = trim($xml->Описание);

        $this->sku  = trim($xml->Артикул);
        $this->unit = trim($xml->БазоваяЕдиница);
        $this->weight = (float)$xml->ВагаУпаковки;
        $this->box = (int)$xml->КількістьВУпаковці;

        if ($xml->Группы) {
            foreach ($xml->Группы->Ид as $categoryId) {
                $this->categories[] = (string)$categoryId;
            }
        }

        if ($xml->ЗначенияРеквизитов) {
            foreach ($xml->ЗначенияРеквизитов->ЗначениеРеквизита as $value) {
                $name                    = (string)$value->Наименование;
                $this->requisites[$name] = (string)$value->Значение;
            }
        }

        if ($xml->Фільтри) {
            foreach ($xml->Фільтри as $filter) {
                
                foreach ($filter as $prop) {
                    $name    = (string)$prop->ua->Назва;
                    // $name_ru    = (string)$prop->ru->Назва;
                    $value = (string)$prop->ua->Значення;
                    // $value_ru = (string)$prop->ru->Значення;

                    if ($value) {
                        $this->properties[$name] = $value;
                        // $this->properties_ru[$name_ru] = $value_ru;
                    }
                }
            }
        }
    }

    /**
     * Load primary data form offers.xml.
     *
     * @param \SimpleXMLElement $xml
     *
     * @return void
     */
    public function loadOffers($xml)
    {
        if ($xml->СрокПоставки)
            $this->termin = (string)$xml->СрокПоставки;

        if ($xml->Количество)
            $this->quantity = (int)$xml->Количество;

        if ($xml->Остаток)
            $this->storage_1 = (int)$xml->Остаток;

        if ($xml->ОстатокПоставщика)
            $this->storage_2 = (int)$xml->ОстатокПоставщика;


        if ($xml->Цены) {
            foreach ($xml->Цены->Цена as $price) {
                $id = (string)$price->ИдТипаЦены;

                $this->price[$id] = [
                    'type'     => $id,
                    'currency' => (string)$price->Валюта,
                    'value'    => (float)$price->ЦенаЗаЕдиницу
                ];
            }
        }
    }

    /**
     * Get price by type.
     *
     * @param string $type
     *
     * @return float
     */
    public function getPrice($type)
    {
        foreach ($this->price as $price) {
            if ($price['type'] == $type) {
                return $price['value'];
            }
        }

        return 0;
    }
}
