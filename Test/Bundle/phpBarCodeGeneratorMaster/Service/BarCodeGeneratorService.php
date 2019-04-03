<?php

namespace Test\Bundle\phpBarCodeGeneratorMaster\Service;

require_once 'custom/plugins/Test/Bundle/phpBarCodeGeneratorMaster/generate-verified-files.php';

class BarCodeGeneratorService
{
    /** @var ContainerInterface */
    private $container;

    /**
     * AttributesCreatorService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    public function getBarCode($numberCode)
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barCod = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($numberCode, $generator::TYPE_CODE_128)) . '">';
        return $barCod;
    }
}