<?php

namespace ZilProductImport\Services\Category;

use Psr\Container\ContainerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Attribute\Category as CategoryAttr;

class Category
{
    const CATEGORIES_DELIMETR = "&&";

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getCategoriesModels(array $categoriesNamePath)
    {
        /** @var ModelManager $model */
        $model = $this->container->get('models');

        $categoryNamePathList = explode(self::CATEGORIES_DELIMETR, $categoriesNamePath);

        $categoriesModels = [];
        foreach ($categoryNamePathList as $key => $categoryNamePath) {
            $categoryId = $this->getCategoryId($categoryNamePath);
            /** @var CategoryModel $categoryModel */
            $categoryModel = $model->getRepository(CategoryModel::class)->find($categoryId);
            if (!empty($categoryModel)) {
                $categoriesModels[] = $categoryModel;
            }
        }

        return $categoriesModels;
    }

    private function getCategoryId($categoryNamePath)
    {
        $categoryNamePathSteps = explode('|', $categoryNamePath);
        $categoryName = end($categoryNamePathSteps);

        $categoryIdPath = $this->generateCategoryIdPath($categoryNamePath);
        $categoryId = Shopware()->Db()->fetchOne(
            "SELECT * FROM `s_categories` WHERE description = ? AND path = ?",
            [$categoryName, $categoryIdPath]
        );

        return $categoryId;
    }

    /* If dosen`t have category with name $categoryPathStep it will be created  */
    private function generateCategoryIdPath($categoryNamePath)
    {
        $categoryIdPath = '|';
        /** @var ModelManager $model */
        $model = $this->container->get('models');
        $categoryPathSteps = explode('|', $categoryNamePath);
        if (!$this->isSubShop($categoryPathSteps[0])) {
            /* If subShop name didn`t written into the $categoryNamPath it will be taken by default */
            /** @var CategoryModel $categoryModel */
            $categoryModel = $model->getRepository(CategoryModel::class)->findOneBy(['parent' => 1, 'name' => 'հայերեն']);
            if (!$categoryModel) {
                $categoryModel = $model->getRepository(CategoryModel::class)->findOneBy(['parent' => 1]);
            }
            $subShopNameByDefault = $categoryModel->getName();
            array_unshift($categoryPathSteps, $subShopNameByDefault);
        }

        $step = 0;
        foreach ($categoryPathSteps as $categoryKey => $categoryName) {
            $step++;
            $path = $categoryIdPath; // $path the same as $categoryIdPath without $categoryId, equal s_categories table`s path column value
            if ($step > 1) {
                $findArgument = [
                    'name' => $categoryName,
                    'path' => $path
                ];
            } else {
                $findArgument = [
                    'name' => $categoryName,
                ];
            }
            /** @var CategoryModel $categoryModelByName */
            $categoryModelByName = $model->getRepository(CategoryModel::class)->findOneBy($findArgument);
            if (empty($categoryModelByName)) {
                $categoryId = $this->createCategory($categoryIdPath, $categoryName);
            } else {
                $categoryId = $categoryModelByName->getId();
            }
            if ($step < count($categoryPathSteps)) {
                $categoryId = '|' . $categoryId;
                $categoryIdPath = $categoryId . $categoryIdPath;
            }
        }

        return $categoryIdPath;
    }


    private function createCategory($categoryIdPath, $categoryName)
    {
        $parentId = explode('|', $categoryIdPath)[1];
        Shopware()->Db()->query("INSERT INTO `s_categories` (parent, path, description, active, blog) VALUES (?, ?, ?, '1', '0')",
            [$parentId, $categoryIdPath, $categoryName]
        );

        /** @var ModelManager $models */
        $models = $this->container->get('models');
        /** @var CategoryModel $categoryModel */
        $categoryModel = $models->getRepository(CategoryModel::class)->findOneBy(['name' => $categoryName, 'path'=>$categoryIdPath]);

        $categoryAttr = new CategoryAttr();
        $categoryAttr->setCategory($categoryModel);
        $models->persist($categoryAttr);
        $models->flush();

        return $categoryModel->getId();
    }

    private function isSubShop($categoryName)
    {
        $result = false;

        /** @var ModelManager $model */
        $model = $this->container->get('models');
        /** @var CategoryModel $categoryModel */
        $categoryModel = $model->getRepository(CategoryModel::class)->findOneBy(['name' => $categoryName]);
        if (empty($categoryModel)) {
            return $result;
        }
        $path = $categoryModel->getPath();
        if (empty($path)) {
            $result = true;
        }

        return $result;
    }
}