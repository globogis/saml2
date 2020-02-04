<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 IDPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class IDPSSODescriptor extends AbstractSSODescriptor
{
    /**
     * Whether AuthnRequests sent to this IdP should be signed.
     *
     * @var bool|null
     */
    protected $wantAuthnRequestsSigned = null;

    /**
     * List of SingleSignOnService endpoints.
     *
     * @var \SAML2\XML\md\SingleSignOnService[]
     */
    protected $ssoServiceEndpoints = [];

    /**
     * List of NameIDMappingService endpoints.
     *
     * @var \SAML2\XML\md\NameIDMappingService[]|null
     */
    protected $nameIDMappingServiceEndpoints = null;

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * @var \SAML2\XML\md\AssertionIDRequestService[]|null
     */
    protected $assertionIDRequestServiceEndpoints = null;

    /**
     * List of supported attribute profiles.
     *
     * @var string[]
     */
    protected $attributeProfiles = [];

    /**
     * List of supported attributes.
     *
     * @var \SAML2\XML\saml\Attribute[]
     */
    protected $attributes = [];


    /**
     * IDPSSODescriptor constructor.
     *
     * @param \SAML2\XML\md\SingleSignOnService[] $ssoServiceEndpoints
     * @param string[] $protocolSupportEnumeration
     * @param bool|null $wantAuthnRequestsSigned
     * @param \SAML2\XML\md\NameIDMappingService[]|null $nameIDMappingServiceEndpoints
     * @param \SAML2\XML\md\AssertionIDRequestService[]|null $assertionIDRequestServiceEndpoints
     * @param string[]|null $attributeProfiles
     * @param \SAML2\XML\saml\Attribute[]|null $attributes
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SAML2\XML\md\KeyDescriptor[]|null $keyDescriptors
     * @param \SAML2\XML\md\Organization|null $organization
     * @param \SAML2\XML\md\ContactPerson[]|null $contacts
     * @param \SAML2\XML\md\ArtifactResolutionService[]|null $artifactResolutionService
     * @param \SAML2\XML\md\SingleLogoutService[]|null $singleLogoutService
     * @param \SAML2\XML\md\ManageNameIDService[]|null $manageNameIDService
     * @param string[]|null $nameIDFormat
     */
    public function __construct(
        array $ssoServiceEndpoints,
        array $protocolSupportEnumeration,
        bool $wantAuthnRequestsSigned = null,
        ?array $nameIDMappingServiceEndpoints = null,
        ?array $assertionIDRequestServiceEndpoints = null,
        ?array $attributeProfiles = null,
        ?array $attributes = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?array $keyDescriptors = [],
        ?Organization $organization = null,
        ?array $contacts = [],
        ?array $artifactResolutionService = [],
        ?array $singleLogoutService = [],
        ?array $manageNameIDService = [],
        ?array $nameIDFormat = []
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
            $contacts,
            $artifactResolutionService,
            $singleLogoutService,
            $manageNameIDService,
            $nameIDFormat
        );
        $this->setSingleSignOnServices($ssoServiceEndpoints);
        $this->setWantAuthnRequestsSigned($wantAuthnRequestsSigned);
        $this->setNameIDMappingServices($nameIDMappingServiceEndpoints);
        $this->setAssertionIDRequestService($assertionIDRequestServiceEndpoints);
        $this->setAttributeProfiles($attributeProfiles);
        $this->setSupportedAttributes($attributes);
    }


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SAML2\XML\md\IDPSSODescriptor
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        return new self(
            SingleSignOnService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim(self::getAttribute($xml, 'protocolSupportEnumeration'))),
            self::getBooleanAttribute($xml, 'WantAuthnRequestsSigned', null),
            NameIDMappingService::getChildrenOfClass($xml),
            AssertionIDRequestService::getChildrenOfClass($xml),
            Utils::extractStrings($xml, Constants::NS_MD, 'AttributeProfile'),
            Attribute::getChildrenOfClass($xml),
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
     * Collect the value of the WantAuthnRequestsSigned-property
     *
     * @return bool|null
     */
    public function wantAuthnRequestsSigned(): ?bool
    {
        return $this->wantAuthnRequestsSigned;
    }


    /**
     * Set the value of the WantAuthnRequestsSigned-property
     *
     * @param bool|null $flag
     */
    protected function setWantAuthnRequestsSigned(?bool $flag = null): void
    {
        $this->wantAuthnRequestsSigned = $flag;
    }


    /**
     * Get the SingleSignOnService endpoints
     *
     * @return \SAML2\XML\md\SingleSignOnService[]
     */
    public function getSingleSignOnServices(): array
    {
        return $this->ssoServiceEndpoints;
    }


    /**
     * Set the SingleSignOnService endpoints
     *
     * @param array $singleSignOnServices
     */
    protected function setSingleSignOnServices(array $singleSignOnServices): void
    {
        Assert::minCount($singleSignOnServices, 1, 'At least one SingleSignOnService must be specified.');
        Assert::allIsInstanceOf(
            $singleSignOnServices,
            SingleSignOnService::class,
            'All md:SingleSignOnService endpoints must be an instance of SingleSignOnService.'
        );
        $this->ssoServiceEndpoints = $singleSignOnServices;
    }


    /**
     * Get the NameIDMappingService endpoints
     *
     * @return \SAML2\XML\md\NameIDMappingService[]|null
     */
    public function getNameIDMappingServices(): ?array
    {
        return $this->nameIDMappingServiceEndpoints;
    }


    /**
     * Set the NameIDMappingService endpoints
     *
     * @param array|null $nameIDMappingServices
     */
    protected function setNameIDMappingServices(?array $nameIDMappingServices): void
    {
        if ($nameIDMappingServices !== null) {
            Assert::allIsInstanceOf(
                $nameIDMappingServices,
                NameIDMappingService::class,
                'All md:NameIDMappingService endpoints must be an instance of NameIDMappingService.'
            );
        }
        $this->nameIDMappingServiceEndpoints = $nameIDMappingServices;
    }


    /**
     * Collect the AssertionIDRequestService endpoints
     *
     * @return \SAML2\XML\md\AssertionIDRequestService[]|null
     */
    public function getAssertionIDRequestServices(): ?array
    {
        return $this->assertionIDRequestServiceEndpoints;
    }


    /**
     * Set the AssertionIDRequestService endpoints
     *
     * @param array $assertionIDRequestServices|null
     */
    protected function setAssertionIDRequestService(?array $assertionIDRequestServices): void
    {
        if ($assertionIDRequestServices !== null) {
            Assert::allIsInstanceOf(
                $assertionIDRequestServices,
                AssertionIDRequestService::class,
                'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.'
            );
        }
        $this->assertionIDRequestServiceEndpoints = $assertionIDRequestServices;
    }


    /**
     * Get the attribute profiles supported
     *
     * @return string[]
     */
    public function getAttributeProfiles(): array
    {
        return $this->attributeProfiles;
    }


    /**
     * Set the attribute profiles supported
     *
     * @param array $attributeProfiles
     */
    protected function setAttributeProfiles(array $attributeProfiles): void
    {
        $this->attributeProfiles = $attributeProfiles;
    }


    /**
     * Get the attributes supported by this IdP
     *
     * @return \SAML2\XML\saml\Attribute[]
     */
    public function getSupportedAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * Set the attributes supported by this IdP
     *
     * @param \SAML2\XML\saml\Attribute[] $attributes
     */
    protected function setSupportedAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }


    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if (is_bool($this->wantAuthnRequestsSigned)) {
            $e->setAttribute('WantAuthnRequestsSigned', $this->wantAuthnRequestsSigned ? 'true' : 'false');
        }

        foreach ($this->ssoServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->nameIDMappingServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->assertionIDRequestServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:AttributeProfile', false, $this->attributeProfiles);

        foreach ($this->attributes as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
