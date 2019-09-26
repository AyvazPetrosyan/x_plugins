<?php

namespace ZilProductImport\Services\InsertData;

use Psr\Container\ContainerInterface;
use Shopware\Components\Model\ModelManager;
use ZilProductImport\Services\Files\FilesService;

class Insert
{
    private $uploadDir;

    private $uploadFilesFormat;

    /* The names of the uploaded files and the names of tables are the same */
    private $uploadFilesName = [];

    private $container;

    public function __construct(ContainerInterface $container, $pluginDir)
    {
        $this->container = $container;
        /** @var FilesService $filesService */
        $filesService = $container->get('zil_product_import.services.FilesService');
        //$this->uploadFilesName = $filesService::FILES_NAME;
        $this->uploadDir = $pluginDir . '/../../../' . $filesService::UPLOAD_DIR . '/' . $filesService::FILES_DIR_NAME;
        $this->uploadFilesFormat = $filesService::FILES_FORMAT;
    }

    public function insertUploadedFiles($filesList)
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        //$filesList = $this->uploadFilesName;
        foreach ($filesList as $fileKey => $fileName) {
            /** @var FilesService $filesService */
            $filesService = $this->container->get('zil_product_import.services.FilesService');
            $fileFullName = $this->uploadDir . "/$fileName"; // . $this->uploadFilesFormat;
            $tableName = 'products'; //$fileName;
            $lastTableName = $tableName . "_last";

            $queryResult = $this->replaceData($tableName, $lastTableName);

            if ($queryResult) {
                $fileContent = [];
                $fOpen = fopen($fileFullName, 'r');
                $step = 0;
                $fieldsHeader = fgetcsv($fOpen, 0, $filesService::CSV_DELIMITER);
                $fieldsHeader[] = 'hash';
                $fileContent[] = $fieldsHeader; // $fileContent[0] should be equal to the first line of the csv file or csv header
                while ($fileContentRow = fgetcsv($fOpen, 0, $filesService::CSV_DELIMITER)) {
                    if ($step == 500) {
                        $step = 0;
                        $insertResult = $this->dataSave($fileContent, $tableName);
                        if (!$insertResult) {
                            $result = [
                                'message' => 'Insert is failed',
                                'result' => false
                            ];
                            break;
                        }
                        $fileContent = [];
                        $fileContent[] = $fieldsHeader; // $fileContent[0] should be equal to the first line of the csv file or csv header
                    }
                    if (count($fileContentRow)>1) {
                        $fileContent[] = $this->setHashCode($fileContentRow);
                    }
                    $step++;
                }

                $insertResult = $this->dataSave($fileContent, $tableName);
                if (!$insertResult) {
                    $result = [
                        'message' => 'Insert is failed',
                        'result' => false
                    ];
                    break;
                }
            }
        }

        Shopware()->Db()->query('UPDATE `products` SET `active`=0');

        return $result;
    }

    private function setHashCode($array)
    {
        $string = '';
        foreach ($array as $key => $value) {
            $string .= $value;
        }
        $array[] = md5($string);
        return $array;
    }

    private function dataSave($data, $tableName)
    {
        try {
            $query = $this->generateQuery($data, $tableName);
            Shopware()->Db()->query($query);
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
            return false;
        }

        return true;
    }

    private function getModelClassName($classNamespace, $tableName)
    {
        $subNames = explode('_', $tableName);
        $modelClassName = '';
        foreach ($subNames as $subNameKey => $subName) {
            $modelClassName .= ucfirst($subName);
        }
        $modelClassName = $classNamespace . "\\" . $modelClassName;

        return $modelClassName;
    }

    private function getModelSetFunction($fileFieldName)
    {
        $subNames = explode('_', $fileFieldName);
        $modelMemberName = '';
        foreach ($subNames as $subNameKey => $subName) {
            $modelMemberName .= ucfirst($subName);
        }

        $setFunction = 'set' . $modelMemberName;

        return $setFunction;
    }

    private function replaceData($fromTable1, $toTable2)
    {
        $result = false;
        Shopware()->Db()->query("DELETE FROM $toTable2");
        $replaceResult = Shopware()->Db()->query("INSERT INTO $toTable2 SELECT * FROM $fromTable1");
        if ($replaceResult) {
            Shopware()->Db()->query("DELETE FROM $fromTable1");
            $result = true;
        }

        return $result;
    }

    private function generateQuery($data, $tableName)
    {
        /* sql query column names part */
        $columnNames = '';
        $step = 1;
        foreach ($data[0] as $columnKey => $columnName) {
            if ($step < count($data[0])) {
                $columnName = '`' . $columnName . '`,';
            } else {
                $columnName = '`' . $columnName . '`';
            }
            $columnNames .= $columnName;
            $step++;
        }

        /* sql query values part */
        $columnsValues = '';
        foreach ($data as $rowKey => $rowInfo) {
            if ($rowKey > 0) {
                $columnsValue = '';
                $step = 1;
                foreach ($rowInfo as $fieldKey => $fieldValue) {
                    if ($step < count($rowInfo)) {
                        $fieldValue = '\'' . $fieldValue . '\',';
                    } else {
                        $fieldValue = '\'' . $fieldValue . '\'';
                    }
                    $columnsValue .= $fieldValue;
                    $step++;
                }
                if ($rowKey < count($data) - 1) {
                    $columnsValue = '(' . $columnsValue . '),';
                } else {
                    $columnsValue = '(' . $columnsValue . ')';
                }
                $columnsValues .= $columnsValue;
            }
        }

        $query = "INSERT INTO $tableName ($columnNames) VALUES $columnsValues";

        return $query;
    }
}