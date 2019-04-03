<?php

namespace Test\Bundle\AttributeService;

use Shopware\Components\Model\ModelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Attribute
{
    /** @var ContainerInterface */
    private $container;
    /** @var  ModelManager */
    private $models;
    /** @var bool  */
    private $arrayMode = true;

    /**
     * AttributesCreatorService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container,
        $modelClass
    )
    {
        $this->container = $container;
        $this->models = $container->get('models');
        $this->arrayMode = true;
    }

    /**
     * @param $modelClass
     * @return array
     */
    public function getAll($modelClass)
    {
        $result = $this->models->getRepository($modelClass)->findAll();
        $result = $this->generateArrayMod($result);

        return $result;
    }

    /**
     * @param $modelClass
     * @param $array
     * @return array|null|object
     */
    public function getOneBy($modelClass, $array)
    {
        $result = $this->models->getRepository($modelClass)->findOneBy($array);
        $result = $this->generateArrayMod($result);

        return $result;
    }

    /**
     * @param $modelClass
     * @param $array
     * @return array
     */
    public function getBy($modelClass, $array)
    {
        $result = $this->models->getRepository($modelClass)->findBy($array);
        $result = $this->generateArrayMod($result);

        return $result;
    }

    /**
     * @param $modelClass
     * @param $array
     * @param $columnName
     * @param $columnVal
     */
    public function setIntoTable($modelClass, $array, $columnName, $columnVal)
    {
        $attrModelFunctionName = 'set'.$columnName;
        $result = $this->models->getRepository($modelClass)->findOneBy($array);
        $result->$attrModelFunctionName($columnVal);
        $this->models->persist($result);
        $this->models->flush();
    }

    /**
     * @param $arrayMode
     */
    public function setArrayMode($arrayMode)
    {
        $this->arrayMode = $arrayMode;
    }

    /**
     * @param $args
     * @return array
     */
    private function generateArrayMod($args)
    {
        if($this->arrayMode){
            $args = $this->models->toArray($args);
        }

        return $args;
    }
}