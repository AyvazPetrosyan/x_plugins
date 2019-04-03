<?php

class Shopware_Controllers_Frontend_VariantAjaxChange extends Enlight_Controller_Action {

    public function indexAction (){
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $request = $this->Request();
        $selection = $request->getParam("group", []);
        $articleID = (int)$request->getParam("articleID");
        $productInfo = $this->getProductInfo($articleID, $selection);
        if($productInfo != null){
            $encodeProductInfo = json_encode($productInfo);
            echo $encodeProductInfo;
        }
    }

    private function getProductInfo ($articleID, $selection){
        try {
            $article = Shopware()->Modules()->Articles()->sGetArticleById(
                $articleID,
                null,
                null,
                $selection
            );
        } catch (RuntimeException $e) {
            $article = null;
        }
        $productInfo['orderNumber'] = $article['ordernumber'];
        $productInfo['price'] = $article['price'];
        return $productInfo;
    }
}