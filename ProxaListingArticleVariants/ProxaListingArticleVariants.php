<?php 
namespace ProxaListingArticleVariants;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProxaListingArticleVariants extends Plugin {
	public function install(InstallContext $context) {
		parent::install($context);
	}

	public function uninstall(UninstallContext $context) {
		parent::uninstall($context);
	}

	public function build(ContainerBuilder $container) {
		$container->setParameter('proxa_listing_article_variants.plugin_dir', $this->getPath());
		parent::build($container);
	}	
}