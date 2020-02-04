<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Tests for the AffiliationDescriptor class.
 *
 * @package simplesamlphp/saml2
 */
final class AffiliationDescriptorTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdNamespace = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S" affiliationOwnerID="TheOwner">
  <md:AffiliateMember>Member</md:AffiliateMember>
  <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );
    }


    /**
     * Test creating an AffiliationDescriptor object from scratch.
     */
    public function testMarshalling(): void
    {
        $ad = new AffiliationDescriptor(
            'TheOwner',
            ['Member', 'OtherMember'],
            null,
            'TheID',
            1234567890,
            'PT5000S'
        );

        $this->assertEquals('TheOwner', $ad->getAffiliationOwnerID());
        $this->assertEquals('TheID', $ad->getID());
        $this->assertEquals('2009-02-13T23:31:30Z', gmdate('Y-m-d\TH:i:s\Z', $ad->getValidUntil()));
        $this->assertEquals('PT5000S', $ad->getCacheDuration());
        $affiliateMembers = $ad->getAffiliateMembers();
        $this->assertCount(2, $affiliateMembers);
        $this->assertEquals('Member', $affiliateMembers[0]);
        $this->assertEquals('OtherMember', $affiliateMembers[1]);
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($ad));
    }


    /**
     * Test that creating an AffiliationDescriptor with an empty owner ID fails.
     */
    public function testMarhsallingWithEmptyOwnerID(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('AffiliationOwnerID must not be empty.');
        new AffiliationDescriptor(
            '',
            ['Member1', 'Member2'],
            [Utils::createKeyDescriptor("testCert")],
            'TheID',
            1234567890,
            'PT5000S'
        );
    }


    /**
     * Test that creating an AffiliationDescriptor with an empty list of members fails.
     */
    public function testMarshallingWithEmptyMemberList(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('List of affiliated members must not be empty.');
        new AffiliationDescriptor(
            'TheOwner',
            [],
            [Utils::createKeyDescriptor("testCert")],
            'TheID',
            1234567890,
            'PT5000S'
        );
    }


    /**
     * Test that creating an AffiliationDescriptor with an empty ID for a member.
     */
    public function testMarshallingWithEmptyMemberID(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot specify an empty string as an affiliation member entityID.');
        new AffiliationDescriptor(
            'TheOwner',
            ['Member1', ''],
            [Utils::createKeyDescriptor("testCert")],
            'TheID',
            1234567890,
            'PT5000S'
        );
    }


    /**
     * Test creating an AffiliationDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="TheOwner" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
    <md:AffiliateMember>Member</md:AffiliateMember>
    <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );

        $affiliateDescriptor = AffiliationDescriptor::fromXML($document->documentElement);
        $this->assertEquals('TheOwner', $affiliateDescriptor->getAffiliationOwnerID());
        $this->assertEquals('TheID', $affiliateDescriptor->getID());
        $this->assertEquals(1234567890, $affiliateDescriptor->getValidUntil());
        $this->assertEquals('PT5000S', $affiliateDescriptor->getCacheDuration());
        $affiliateMember = $affiliateDescriptor->getAffiliateMembers();
        $this->assertCount(2, $affiliateMember);
        $this->assertEquals('Member', $affiliateMember[0]);
        $this->assertEquals('OtherMember', $affiliateMember[1]);
    }


    /**
     * Test failure to create an AffiliationDescriptor from XML when there's no affiliation members.
     */
    public function testUnmarshallingWithoutMembers(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="TheOwner" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
</md:AffiliationDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('List of affiliated members must not be empty.');
        AffiliationDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test failure to create an AffiliationDescriptor from XML when there's an empty affiliation member.
     */
    public function testUnmarshallingWithEmptyMember(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="TheOwner" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
    <md:AffiliateMember></md:AffiliateMember>
    <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot specify an empty string as an affiliation member entityID.');
        AffiliationDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test failure to create an AffiliationDescriptor from XML when there's no owner specified.
     */
    public function testUnmarshallingWithoutOwner(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
    <md:AffiliateMember>Member</md:AffiliateMember>
    <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing affiliationOwnerID on AffiliationDescriptor.');
        AffiliationDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test serialization and unserialization of AffiliationDescriptor elements.
     */
    public function testSerialization(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S" affiliationOwnerID="TheOwner">
  <md:AffiliateMember>Member</md:AffiliateMember>
  <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );
        $ad = AffiliationDescriptor::fromXML($document->documentElement);
        $this->assertEquals($document->saveXML($document->documentElement), strval(unserialize(serialize($ad))));
    }
}
