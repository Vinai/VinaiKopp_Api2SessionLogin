<?php


class VinaiKopp_Api2SessionLogin_ExtensionTest extends VinaiKopp_Framework_TestCase
{
    public function testExtensionInstalled()
    {
        $config = Mage::getConfig()->getNode('modules/VinaiKopp_Api2SessionLogin/active');
        $this->assertEquals('true', "$config");
    }
}