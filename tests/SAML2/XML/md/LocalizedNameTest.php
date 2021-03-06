<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
class LocalizedNameTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $ns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:OrganizationName xml:lang="en" xmlns:md="{$ns}">Names R US</md:OrganizationName>
XML
        );
    }


    /**
     * Test creating a LocalizedName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationName('en', 'Names R US');
        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('Names R US', $name->getValue());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a LocalizedName from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');
        new OrganizationName('', 'Names R US');
    }


    /**
     * Test that creating a LocalizedName from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new OrganizationName('en', '');
        $this->document->documentElement->textContent = '';
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test creating a LocalizedName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationName::fromXML($this->document->documentElement);
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test that creating a LocalizedName from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing xml:lang from OrganizationName');
        $this->document->documentElement->removeAttributeNS(AbstractLocalizedName::XML_NS, 'lang');
        OrganizationName::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a LocalizedName from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');
        $this->document->documentElement->setAttributeNS(AbstractLocalizedName::XML_NS, 'lang', '');
        OrganizationName::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a LocalizedName from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->document->documentElement->textContent = '';
        $name = OrganizationName::fromXML($this->document->documentElement);
        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($name));
    }


    /**
     * Test serialization / unserialization.
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(OrganizationName::fromXML($this->document->documentElement))))
        );
    }
}
