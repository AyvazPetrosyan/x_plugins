<?php

namespace ZilProductImport\Services\Validation;

use Psr\Container\ContainerInterface;
use ZilProductImport\Services\Files\FilesService;

class CsvValidationService
{
    /** @var ContainerInterface */
    private $container;

    /** @var FilesService */
    private $filesService;

    /** @var ValidationTypesService */
    private $validationTypesService;

    private $csvDir;

    public function __construct(
        ContainerInterface $container,
        FilesService $filesService,
        ValidationTypesService $validationTypesService,
        $pluginDir)
    {
        $this->container = $container;
        $this->filesService = $filesService;
        $this->validationTypesService = $validationTypesService;
        $this->csvDir = $filesService->csvDir; //$pluginDir . '/' . $filesService::UPLOAD_DIR . '/' . $filesService::FILES_DIR_NAME;
    }

    public function validation()
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        $filesValidation = $this->filesNameValidation();
        $filesContentValidation = $this->filesContentValidation();
        if (!$filesValidation['result']) {
            $result = $filesValidation;
        } elseif (!$filesContentValidation['result']) {
            $result = $filesContentValidation;
        }

        return $result;
    }

    public function filesNameValidation()
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        $productsCsv = [];

        $filesService = $this->filesService;
        $filesName = $filesService::FILES_NAME;
        $filesFormat = $filesService::FILES_FORMAT;

        $uploadedFiles = scandir($this->csvDir);
        foreach ($uploadedFiles as $uploadedFile) {
            if($uploadedFile == "." || $uploadedFile == "..") {
                continue;
            }
            $uploadedFileName = explode('.', basename($uploadedFile))[0];

            //if ($uploadedFileName == $filesName['productsName']) {
                $uploadedFileFormat = pathinfo($uploadedFile, PATHINFO_EXTENSION);
                $productsCsv = [
                    'name' => $uploadedFileName,
                    'format' => $uploadedFileFormat
                ];
            //}
        }

        if (/*$productsCsv['name'] != $filesName['productsName'] ||*/ $productsCsv['format'] != $filesFormat) {
            $result = [
                'message' => 'The uploaded file format must be csv', //$this->generateFileExceptionErrorMessage($filesName['productsName'], $filesFormat),
                'result' => false
            ];
        }

        return $result;
    }

    public function filesContentValidation()
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        $filesService = $this->filesService;
        $uploadedFiles = scandir($this->csvDir);

        $step = 0;
        foreach ($uploadedFiles as $csvKey => $csv) {
            if ($csv == '.' || $csv == '..') {
                continue;
            }
            $handle = fopen($this->csvDir . "/" . $csv, 'r');
            if ($handle !== false /*&& $step > 1 && $csv == 'products.csv'*/) {
                $csvData = fgetcsv($handle, 0, $filesService::CSV_DELIMITER);
                $result = $this->fieldNameValidation($csvData, $csv);
                if ($result['result'] == false) {
                    return $result;
                }
                $result = $this->fieldsValueValidation($csv);
                if ($result['result'] == false) {
                    return $result;
                }
            }
            $step++;
            fclose($handle);
        }

        return $result;
    }

    private function fieldNameValidation($fieldsName, $fileFullName)
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        $fileName = explode('.', $fileFullName)[0];

        $filesService = $this->filesService;
        $validationTypeService = $this->validationTypesService;

        //if ($fileName == $filesService::FILES_NAME['productsName']) {
            $definedFieldsName = $filesService->getProductsFieldsNameList();
        //} else {
        //    return $result;
        //}

        foreach ($definedFieldsName as $definedFieldsKey => $definedFieldsInfo) {
            $check = false;
            foreach ($definedFieldsInfo['nameValidation'] as $validationType => $validationTypeValue) {
                if ($validationType == $validationTypeService::FIXED_VALUE) {
                    foreach ($fieldsName as $fieldKey => $fieldName) {
                        if ($fieldName == $definedFieldsInfo['name']) {
                            $check = true;
                            break;
                        }
                    }
                } else {
                    $check = true;
                }
            }
            if (empty($definedFieldsInfo['nameValidation'])) {
                $check = true;
            }
            if (!$check) {
                $result = [
                    'message' => $this->generateFieldExceptionErrorMessage($fileFullName, $definedFieldsInfo['name']),
                    'result' => false
                ];

                return $result;
            }
        }

        return $result;
    }

    private function fieldsValueValidation($fileFullName)
    {
        $result = [
            'message' => '',
            'result' => true
        ];
        $filePath = $this->csvDir . "/" . $fileFullName;

        $validationTypeService = $this->validationTypesService;
        /** @var FilesService $filesService */
        $filesService = $this->container->get('zil_product_import.services.FilesService');

        $fileContent = [];
        $fOpen = fopen($filePath, 'r');
        $fieldsHeader = fgetcsv($fOpen, 0, $filesService::CSV_DELIMITER);
        //$fieldsHeader = $filesService->csvToArray($filesService->csvDir . "/" . $fileFullName);
        //$fieldsHeader = array_keys($fieldsHeader[0]);
        $fileContent[] = $fieldsHeader;
        $step = 1;
        $fileRowNumber = 1;
        //$csvContent =  $filesService->csvToArray($filesService->csvDir . "/" . $fileFullName);
        while ($fileContentRow = fgetcsv($fOpen, 0, $filesService::CSV_DELIMITER)) {
        //foreach ($csvContent as $fileContentRowKey=>$fileContentRow) {
            if ($step%200 == 0) {
                foreach ($fileContent as $rowKey => $row) {
                    $fileRowNumber = $rowKey + $step - 199;
                    foreach ($row as $fieldKey => $fieldValue) {
                        $validationTypes = $this->getFieldValidationTypes($fieldsHeader[$fieldKey]);
                        foreach ($validationTypes['valueValidation'] as $validationType => $validationTypeValue) {
                            if ($validationType == $validationTypeService::NOT_EMPTY && empty($fieldValue) && strlen($fieldValue)==0) {
                                $result['message'] .= 'The ' . $fieldsHeader[$fieldKey] . ' field can not be empty on line ' . $step . '</br>';
                            }
                        }
                    }
                }
                $fileContent = [];
                //$step = 1;
            }
            $fileContent[] = $fileContentRow;
            $step++;
        }

        array_pop($fileContent);

        foreach ($fileContent as $rowKey => $row) {
            $fileRowNumber++;
            foreach ($row as $fieldKey => $fieldValue) {
                $validationTypes = $this->getFieldValidationTypes($fieldsHeader[$fieldKey]);
                foreach ($validationTypes['valueValidation'] as $validationType => $validationTypeValue) {
                    if ($validationType == $validationTypeService::NOT_EMPTY && empty($fieldValue) && strlen($fieldValue)==0) {
                        $result['message'] .= 'The ' . $fieldsHeader[$fieldKey] . ' field can not be empty on line ' . $step . '</br>';
                    }
                }
            }
            $step++;
        }

        if (!empty($result['message'])) {
            $result['result'] = false;
        }

        return $result;
    }

    private function getFieldValidationTypes($fieldName)
    {
        $validationTypes = [];
        $filesService = $this->filesService;
        $definedFieldsName = $filesService->getProductsFieldsNameList();
        foreach ($definedFieldsName as $fieldsKey => $definedField) {
            if ($fieldName == $definedField['name']) {
                $validationTypes['nameValidation'] = $definedField['nameValidation'];
                $validationTypes['valueValidation'] = $definedField['valueValidation'];
            }
        }

        return $validationTypes;
    }

    private function generateFileExceptionErrorMessage($fileName, $fileFormat)
    {
        $errorMessage = $fileName . "." . $fileFormat . " file does not exist";
        return $errorMessage;
    }

    private function generateFieldExceptionErrorMessage($fileName, $fieldName)
    {
        $errorMessage = "'$fieldName' fields does not exist in $fileName";
        return $errorMessage;
    }
}