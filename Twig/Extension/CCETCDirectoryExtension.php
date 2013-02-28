<?php
namespace CCETC\DirectoryBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;

class CCETCDirectoryExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getGlobals() {
        return array(
            'directoryTitle' => $this->container->getParameter('ccetc_directory.title'),
            'directoryLogo' => $this->container->getParameter('ccetc_directory.logo'),
            'directoryMenuBuilder' => $this->container->getParameter('ccetc_directory.menu_builder'),
            'layoutTemplate' => $this->container->getParameter('ccetc_directory.layout_template'),
            'headerTemplate' => $this->container->getParameter('ccetc_directory.header_template'),
            'footerTemplate' => $this->container->getParameter('ccetc_directory.footer_template'),
            'listingBlockTemplate' => $this->container->getParameter('ccetc_directory.listing_block_template'),
            'directoryContactEmail' => $this->container->getParameter('ccetc_directory.contact_email'),            
            'directoryCopyright' => $this->container->getParameter('ccetc_directory.copyright'),
            'directoryOgDescription' => $this->container->getParameter('ccetc_directory.og_description'),            
            'directoryOgURL' => $this->container->getParameter('ccetc_directory.og_url'),            
            'googleMapsKey' => $this->container->getParameter('ccetc_directory.google_maps_key'),            
            'googleAnalyticsAccount' => $this->container->getParameter('ccetc_directory.google_analytics_account'),            
        );
    }
    
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'CCETCDirectoryBundle';
    }
}