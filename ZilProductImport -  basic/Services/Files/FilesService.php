<?php

namespace ZilProductImport\Services\Files;

use Psr\Container\ContainerInterface;
use ZilProductImport\Services\Validation\ValidationTypesService;

class FilesService
{
    const UPLOAD_DIR = 'files/public';

    const FILES_DIR_NAME = 'products';

    const FILES_FORMAT = 'csv';

    const ZIP_NAME = 'products';

    const ZIP_FORMAT = 'zip';

    const MEDIA_DIR_NAME = 'media';

    /* The names of the uploaded files and the names of tables are the same */
    const FILES_NAME = [
        'productsName' => 'products',
    ];

    const CSV_DELIMITER = ';';

    const NEW_PRODUCT_STATUS = 'new';

    const UPDATED_PRODUCT_STATUS = 'updated';

    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'JPG', 'png'];

    const PRODUCT_IMPORT_STAT = 'Product import';

    const PRODUCT_UPDATE_STATE = 'Product update';

    const CATEGORY_UPDATE_STATE = 'Category state';

    const MEDIA_PACKAGE_STATUSES = [
        'success' => 'Finished',
        'inProcess' => 'In process',
        'filed' => 'Filed'
    ];

    const THUMBNAIL_SIZES = [140, 200, 294, 600, 1280];

    const ALBUM_SETTING_THUMBNAIL_SIZES = ['140x140', '200x200', '294x294', '600x600', '1280x1280'];

    public $pluginDir;

    public $csvDir;

    private $productsFieldsNameList = [];

    /** @var ContainerInterface */
    private $container;

    /** @var ValidationTypesService */
    private $validationTypeService;

    public function __construct(ContainerInterface $container, $pluginDir)
    {
        $this->container = $container;
        $this->validationTypeService = $container->get('zil_product_import.services.ValidationTypesService');
        $this->pluginDir = $pluginDir;

        $this->csvDir = $pluginDir . '/../../../' . self::UPLOAD_DIR . '/' . self::FILES_DIR_NAME;

        $this->generateProductsFieldsNameList();
    }

    public function getProductsFieldsNameList()
    {
        return $this->productsFieldsNameList;
    }

    public function csvToArray($filename, $delimiter = ';') {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        //check if csv has wrong or mixed encodings like Microsoft excel does it sometimes and try to fix that.
        $header = null;
        $dataList = array();
        $index = 0;
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, null, $delimiter, '"')) !== false) {
                if (!$header) {
                    foreach ($row as &$item) {
                        $item = strtolower($item);
                        $item = str_replace(" ", "_", $item);
                        //$item = str_replace("-", "_", $item);
                    }
                    $header = $row;
                } else {
                    foreach ($row as &$item) {
                        //german excel saves numbers in csv-files with , instead of . so this has to be catched and converted
                        if (preg_match('|^[0-9]+,[0-9]+$|', $item) === 1)
                            $item = (float) str_replace(',', '.', $item);
                    }
                    if( count($header) == count($row) ) {
                        $dataList[] = array_combine($header, $row);
                    }
                    $index++;
                }
            }
            fclose($handle);
        }

        $resDataList = array();
        foreach ($dataList as $data) {
            if (!empty($data)) {
                $resDataList[] = $data;
            }
        }

        return $resDataList;
    }

    private function generateProductsFieldsNameList()
    {
        $validationType = $this->validationTypeService;

        $this->productsFieldsNameList = [
            'productNumber' => [
                'name' => 'product_number',
                'valueValidation' => [
                    $validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'product_number'
                ]
            ],
            'parentNumber' => [
                'name' => 'parent_number',
                'valueValidation' => [
                    $validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'parent_number'
                ]
            ],
            'name' => [
                'name' => 'name',
                'valueValidation' => [
                    $validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'name'
                ]
            ],
            'manufacturer' => [
                'name' => 'manufacturer',
                'valueValidation' => [
                    $validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'manufacturer'
                ]
            ],
            'firstPrice' => [
                'name' => 'Last Price',
                'valueValidation' => [
                    $validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'First Price'
                ]
            ],
            'lastPrice' => [
                'name' => 'Last Price',
                'valueValidation' => [],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'Last Price'
                ]
            ],
            'descriptionEn' => [
                'name' => 'description_en',
                'valueValidation' => [],
                'nameValidation' => [
                    //$validationType::FIXED_VALUE => 'description_en'
                ]
            ],
            'descriptionRu' => [
                'name' => 'description_ru',
                'valueValidation' => [],
                'nameValidation' => [
                    //$validationType::FIXED_VALUE => 'description_ru'
                ]
            ],
            'descriptionAm' => [
                'name' => 'description_am',
                'valueValidation' => [],
                'nameValidation' => [
                    //$validationType::FIXED_VALUE => 'description_am'
                ]
            ],
            'composure' => [
                'name' => 'composure',
                'valueValidation' => [],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'composer'
                ]
            ],
            'size' => [
                'name' => 'size',
                'valueValidation' => [],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'size'
                ]
            ],
            'color' => [
                'name' => 'color',
                'valueValidation' => [],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'color'
                ]
            ],
            'stock' => [
                'name' => 'stock',
                'valueValidation' => [],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'stock'
                ]
            ],
            'image' => [
                'name' => 'image',
                'valueValidation' => [],
                'nameValidation' => []
            ],
            'isMain' => [
                'name' => 'is_main',
                'valueValidation' => [
                    $validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'is_main'
                ]
            ],
            'categories' => [
                'name' => 'categories',
                'valueValidation' => [],
                'nameValidation' => [
                    $validationType::FIXED_VALUE => 'categories'
                ]
            ],
            'active' => [
                'name' => 'active',
                'valueValidation' => [
                    //$validationType::NOT_EMPTY => ''
                ],
                'nameValidation' => [
                    //$validationType::FIXED_VALUE => 'active'
                ]
            ],
            'isOnlineShop' => [
                'name' => 'is_online_shop',
                'valueValidation' => [],
                'nameValidation' => [
                    //$validationType::FIXED_VALUE => 'is_online_shop'
                ]
            ]
        ];
    }
}