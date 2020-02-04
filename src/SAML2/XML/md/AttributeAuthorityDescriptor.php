<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 metadata AttributeAuthorityDescriptor.
 *
 * @package SimpleSAMLphp
 */
final class AttributeAuthorityDescriptor extends AbstractRoleDescriptor
{

    /**
     * List of AttributeService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var AttributeService[]
     */
    protected $AttributeServices = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var AssertionIDRequestService[]
     */
    protected $AssertionIDRequestServices = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    protected $NameIDFormats = [];

    /**
     * List of supported attribute profiles.
     *
     * Array with strings.
     *
     * @var array
     */
    protected $AttributeProfiles = [];

    /**
     * List of supported attributes.
     *
     * Array with \SAML2\XML\saml\Attribute objects.
     *
     * @var Attribute[]
     */
    protected $Attributes = [];


    /**
     * AttributeAuthorityDescriptor constructor.
     *
     * @param AttributeService[] $attributeServices
     * @param string[] $protocolSupportEnumeration
     * @param AssertionIDRequestService[]|null $assertionIDRequestService
     * @param string[]|null $nameIDFormats
     * @param string[]|null $attributeProfiles
     * @param Attribute[]|null $attributes
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param Extensions|null $extensions
     * @param string|null $errorURL
     * @param KeyDescriptor[]|null $keyDescriptors
     * @param Organization|null $organization
     * @param ContactPerson[]|null $contacts
     */
    public function __construct(
        array $attributeServices,
        array $protocolSupportEnumeration,
        ?array $assertionIDRequestService = null,
        ?array $nameIDFormats = null,
        ?array $attributeProfiles = null,
        ?array $attributes = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?array $keyDescriptors = null,
        ?Organization $organization = null,
        ?array $contacts = null
    ) {
        parent::__construct(
            $protocolSupportEnumeration,
            $ID,
            $validUntil,
            $cacheDuration,
            $extensions,
            $errorURL,
            $keyDescriptors,
            $organization,
            $contacts
        );
        $this->setAttributeServices($attributeServices);
        $this->setAssertionIDRequestServices($assertionIDRequestService);
        $this->setNameIDFormats($nameIDFormats);
        $this->setAttributeProfiles($attributeProfiles);
        $this->setAttributes($attributes);
    }


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param DOMElement $xml The XML element we should load.
     *
     * @return self
     * @throws Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        $attrServices = [];
        /** @var DOMElement $ep */
        foreach (Utils::xpQuery($xml, './saml_metadata:AttributeService') as $ep) {
            $attrServices[] = AttributeService::fromXML($ep);
        }
        if ($attrServices === []) {
            throw new Exception('Must have at least one AttributeService in AttributeAuthorityDescriptor.');
        }

        $assertIDReqServices = [];
        /** @var DOMElement $ep */
        foreach (Utils::xpQuery($xml, './saml_metadata:AssertionIDRequestService') as $ep) {
            $assertIDReqServices[] = AssertionIDRequestService::fromXML($ep);
        }

        $nameIDFormats = Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');
        $attrProfiles = Utils::extractStrings($xml, Constants::NS_MD, 'AttributeProfile');

        $attributes = [];
        /** @var DOMElement $a */
        foreach (Utils::xpQuery($xml, './saml_assertion:Attribute') as $a) {
            $attributes[] = Attribute::fromXML($a);
        }

        $validUntil = self::getAttribute($xml, 'validUntil', null);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        return new self(
            $attrServices,
            preg_split('/[\s]+/', trim(self::getAttribute($xml, 'protocolSupportEnumeration'))),
            $assertIDReqServices,
            $nameIDFormats,
            $attrProfiles,
            $attributes,
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml)
        );
    }


    /**
     * Collect the value of the AttributeService-property
     *
     * @return AttributeService[]
     */
    public function getAttributeServices(): array
    {
        return $this->AttributeServices;
    }


    /**
     * Set the value of the AttributeService-property
     *
     * @param AttributeService[] $attributeServices
     *
     * @return void
     */
    protected function setAttributeServices(array $attributeServices): void
    {
        Assert::minCount(
            $attributeServices,
            1,
            'AttributeAuthorityDescriptor must contain at least one AttributeService.'
        );
        Assert::allIsInstanceOf(
            $attributeServices,
            AttributeService::class,
            'AttributeService is not an instance of EndpointType.'
        );
        $this->AttributeServices = $attributeServices;
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return string[]
     */
    public function getNameIDFormats(): array
    {
        return $this->NameIDFormats;
    }


    /**
     * Set the value of the NameIDFormat-property
     *
     * @param string[]|null $nameIDFormats
     *
     * @return void
     */
    protected function setNameIDFormats(?array $nameIDFormats): void
    {
        if ($nameIDFormats === null) {
            return;
        }
        Assert::allStringNotEmpty($nameIDFormats, 'NameIDFormat cannot be an empty string.');
        $this->NameIDFormats = $nameIDFormats;
    }


    /**
     * Collect the value of the AssertionIDRequestService-property
     *
     * @return AssertionIDRequestService[]
     */
    public function getAssertionIDRequestServices(): array
    {
        return $this->AssertionIDRequestServices;
    }


    /**
     * Set the value of the AssertionIDRequestService-property
     *
     * @param AssertionIDRequestService[] $assertionIDRequestServices
     */
    protected function setAssertionIDRequestServices(?array $assertionIDRequestServices): void
    {
        if ($assertionIDRequestServices === null) {
            return;
        }

        Assert::allIsInstanceOf($assertionIDRequestServices, AssertionIDRequestService::class);
        $this->AssertionIDRequestServices = $assertionIDRequestServices;
    }


    /**
     * Collect the value of the AttributeProfile-property
     *
     * @return string[]
     */
    public function getAttributeProfiles(): array
    {
        return $this->AttributeProfiles;
    }


    /**
     * Set the value of the AttributeProfile-property
     *
     * @param string[]|null $attributeProfiles
     */
    protected function setAttributeProfiles(?array $attributeProfiles): void
    {
        if ($attributeProfiles === null) {
            return;
        }
        Assert::allStringNotEmpty($attributeProfiles, 'AttributeProfile cannot be an empty string.');
        $this->AttributeProfiles = $attributeProfiles;
    }


    /**
     * Collect the value of the Attribute-property
     *
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->Attributes;
    }


    /**
     * Set the value of the Attribute-property
     *
     * @param Attribute[]|null $attributes
     */
    protected function setAttributes(?array $attributes): void
    {
        if ($attributes === null) {
            return;
        }
        Assert::allIsInstanceOf($attributes, Attribute::class);
        $this->Attributes = $attributes;
    }


    /**
     * Add this AttributeAuthorityDescriptor to an EntityDescriptor.
     *
     * @param DOMElement|null $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     *
     * @return DOMElement
     *
     * @throws InvalidArgumentException if assertions are false
     * @throws Exception
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->AttributeServices as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->AssertionIDRequestServices as $ep) {
            $ep->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormats);
        Utils::addStrings($e, Constants::NS_MD, 'md:AttributeProfile', false, $this->AttributeProfiles);

        foreach ($this->Attributes as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
