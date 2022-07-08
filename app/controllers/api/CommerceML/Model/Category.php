<?php namespace Zenwalker\CommerceML\Model;

use Zenwalker\CommerceML\ORM\Model;

class Category extends Model
{
    /**
     * @var string $id
     */
    public $id;

    /**
     * @var string $name
     */
    public $name;
    public $name_ru;

    /**
     * @var string $parent
     */
    public $parent;

    /**
     * Create instance from file.
     *
     * @param null $importXml
     * @return \Zenwalker\CommerceML\Model\Category
     */
    public function __construct($importXml = null)
    {
        if (! is_null($importXml)) {
            $this->loadImport($importXml);
        }
    }

    /**
     * Load category data from import.xml.
     *
     * @param SimpleXMLElement $xml
     * @return void
     */
    public function loadImport($xml)
    {
        $this->id = (string) $xml->Ид;

        $this->name = (string) $xml->Наименование;
        $this->name_ru = (string) $xml->Наименование_ru;
    }

    /**
     * Add children category.
     *
     * @param Category $category
     * @return void
     */
    public function addChild($category)
    {
        $category->parent = $this->id;
    }

    /**
     * Add products to category.
     * 
     * @param Collection $products
     * @return void
     */
    public function attachProducts($products)
    {
        $this->products = array();
        foreach ($products->fetch() as $product) {
            if (array_key_exists($this->id, $product->categories)) {
                $this->products[$product->id] = $product;
            }
        }
    }
}
