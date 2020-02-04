<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 EntityDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class EntityDescriptor extends AbstractMetadataDocument
{
    /**
     * The entityID this EntityDescriptor represents.
     *
     * @var string
     */
    protected $entityID;

    /**
     * Array with all roles for this entity.
     *
     * Array of \SAML2\XML\md\RoleDescriptor objects (and subclasses of RoleDescriptor).
     *
     * @var \SAML2\XML\md\AbstractRoleDescriptor[]
     */
    protected $RoleDescriptor = [];

    /**
     * AffiliationDescriptor of this entity.
     *
     * @var \SAML2\XML\md\AffiliationDescriptor|null
     */
    protected $AffiliationDescriptor = null;

    /**
     * Organization of this entity.
     *
     * @var \SAML2\XML\md\Organization|null
     */
    protected $Organization = null;

    /**
     * ContactPerson elements for this entity.
     *
     * @var \SAML2\XML\md\ContactPerson[]
     */
    protected $ContactPerson = [];

    /**
     * AdditionalMetadataLocation elements for this entity.
     *
     * @var \SAML2\XML\md\AdditionalMetadataLocation[]
     */
    protected $AdditionalMetadataLocation = [];


    /**
     * Initialize an EntitiyDescriptor.
     *
     * @param string $entityID The entityID of the entity described by this descriptor.
     * @param string|null $id The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validify for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SAML2\XML\md\Extensions|null $extensions An array of extensions.
     * @param \SAML2\XML\md\AbstractRoleDescriptor[]|null $roleDescriptors An array of role descriptors.
     * @param \SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor An affiliation descriptor to use instead
     * of role descriptors.
     * @param \SAML2\XML\md\Organization|null $organization The organization responsible for the SAML entity.
     * @param \SAML2\XML\md\ContactPerson[]|null $contacts A list of contact persons for this SAML entity.
     * @param \SAML2\XML\md\AdditionalMetadataLocation[]|null $additionalMdLocations A list of additional metadata
     * locations.
     *
     * @throws \Exception
     */
    public function __construct(
        string $entityID,
        ?string $id = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        Extensions $extensions = null,
        array $roleDescriptors = [],
        ?AffiliationDescriptor $affiliationDescriptor = null,
        ?Organization $organization = null,
        array $contacts = [],
        array $additionalMdLocations = []
    ) {
        if (empty($roleDescriptors) && $affiliationDescriptor === null) {
            throw new \Exception(
                'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
            );
        }

        parent::__construct($id, $validUntil, $cacheDuration, $extensions);

        $this->entityID = $entityID;
        $this->setRoleDescriptor($roleDescriptors);
        $this->AffiliationDescriptor = $affiliationDescriptor;
        $this->Organization = $organization;
        $this->setContactPerson($contacts);
        $this->setAdditionalMetadataLocation($additionalMdLocations);
    }


    /**
     * Convert an existing XML into an EntityDescriptor object
     *
     * @param \DOMElement $xml An existing EntityDescriptor XML document.
     *
     * @return \SAML2\XML\md\EntityDescriptor An object representing the given document.
     * @throws \Exception If an error occurs while processing the XML document.
     */
    public static function fromXML(DOMElement $xml): object
    {
        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        $roleDescriptors = [];
        $affiliationDescriptor = null;
        $organization = null;
        $contactPersons = [];
        $additionalMetadataLocation = [];
        foreach ($xml->childNodes as $node) {
            if (!($node instanceof DOMElement)) {
                continue;
            }

            if ($node->namespaceURI !== Constants::NS_MD) {
                continue;
            }

            switch ($node->localName) {
                case 'IDPSSODescriptor':
                    $roleDescriptors[] = IDPSSODescriptor::fromXML($node);
                    break;
                case 'SPSSODescriptor':
                    $roleDescriptors[] = new SPSSODescriptor($node);
                    break;
                case 'AuthnAuthorityDescriptor':
                    $roleDescriptors[] = AuthnAuthorityDescriptor::fromXML($node);
                    break;
                case 'AttributeAuthorityDescriptor':
                    $roleDescriptors[] = AttributeAuthorityDescriptor::fromXML($node);
                    break;
                case 'PDPDescriptor':
                    $roleDescriptors[] = PDPDescriptor::fromXML($node);
                    break;
                case 'AffiliationDescriptor':
                    if ($affiliationDescriptor !== null) {
                        throw new \Exception('More than one AffiliationDescriptor in the entity.');
                    }
                    $affiliationDescriptor = AffiliationDescriptor::fromXML($node);
                    break;
                case 'Organization':
                    if ($organization !== null) {
                        throw new \Exception('More than one Organization in the entity.');
                    }
                    $organization = Organization::fromXML($node);
                    break;
                case 'ContactPerson':
                    $contactPersons[] = ContactPerson::fromXML($node);
                    break;
                case 'AdditionalMetadataLocation':
                    $additionalMetadataLocation[] = AdditionalMetadataLocation::fromXML($node);
                    break;
                default:
                    $roleDescriptors[] = UnknownRoleDescriptor::fromXML($node);
            }
        }

        if (empty($roleDescriptors) && is_null($affiliationDescriptor)) {
            throw new \Exception(
                'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
            );
        } elseif (!empty($roleDescriptors) && !is_null($affiliationDescriptor)) {
            throw new \Exception(
                'AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.'
            );
        }

        return new self(
            self::getAttribute($xml, 'entityID'),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            $roleDescriptors,
            $affiliationDescriptor,
            $organization,
            $contactPersons,
            $additionalMetadataLocation
        );
    }


    /**
     * Collect the value of the entityID property.
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getEntityID(): string
    {
        Assert::notEmpty($this->entityID);

        return $this->entityID;
    }


    /**
     * Set the value of the entityID-property
     * @param string $entityId
     * @return void
     */
    protected function setEntityID(string $entityId): void
    {
        $this->entityID = $entityId;
    }


    /**
     * Collect the value of the RoleDescriptor property.
     *
     * @return \SAML2\XML\md\AbstractRoleDescriptor[]
     */
    public function getRoleDescriptor(): array
    {
        return $this->RoleDescriptor;
    }


    /**
     * Set the value of the RoleDescriptor property.
     *
     * @param \SAML2\XML\md\AbstractRoleDescriptor[] $roleDescriptor
     *
     * @return void
     */
    protected function setRoleDescriptor(array $roleDescriptor): void
    {
        $this->RoleDescriptor = $roleDescriptor;
    }


    /**
     * Collect the value of the AffiliationDescriptor property.
     *
     * @return \SAML2\XML\md\AffiliationDescriptor|null
     */
    public function getAffiliationDescriptor(): ?AffiliationDescriptor
    {
        return $this->AffiliationDescriptor;
    }


    /**
     * Set the value of the AffliationDescriptor property.
     *
     * @param \SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor
     * @return void
     */
    protected function setAffiliationDescriptor(AffiliationDescriptor $affiliationDescriptor = null): void
    {
        $this->AffiliationDescriptor = $affiliationDescriptor;
    }


    /**
     * Collect the value of the Organization property.
     *
     * @return \SAML2\XML\md\Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->Organization;
    }


    /**
     * Set the value of the Organization property.
     *
     * @param \SAML2\XML\md\Organization|null $organization
     * @return void
     */
    protected function setOrganization(Organization $organization = null): void
    {
        $this->Organization = $organization;
    }


    /**
     * Collect the value of the ContactPerson property.
     *
     * @return \SAML2\XML\md\ContactPerson[]
     */
    public function getContactPerson(): array
    {
        return $this->ContactPerson;
    }


    /**
     * Set the value of the ContactPerson property.
     *
     * @param array $contactPerson
     * @return void
     */
    protected function setContactPerson(array $contactPerson): void
    {
        $this->ContactPerson = $contactPerson;
    }


    /**
     * Collect the value of the AdditionalMetadataLocation property.
     *
     * @return \SAML2\XML\md\AdditionalMetadataLocation[]
     */
    public function getAdditionalMetadataLocation(): array
    {
        return $this->AdditionalMetadataLocation;
    }


    /**
     * Set the value of the AdditionalMetadataLocation property.
     *
     * @param array $additionalMetadataLocation
     * @return void
     */
    protected function setAdditionalMetadataLocation(array $additionalMetadataLocation): void
    {
        $this->AdditionalMetadataLocation = $additionalMetadataLocation;
    }


    /**
     * Create this EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntityDescriptor to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->entityID, 'Cannot convert EntityDescriptor to XML without an EntityID set.');

        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_MD, 'md:EntityDescriptor');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:EntityDescriptor');
            $parent->appendChild($e);
        }

        $e->setAttribute('entityID', $this->entityID);

        if ($this->ID !== null) {
            $e->setAttribute('ID', $this->ID);
        }

        if ($this->validUntil !== null) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if ($this->cacheDuration !== null) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        if (!empty($this->Extensions)) {
            $this->Extensions->toXML($e);
        }

        foreach ($this->RoleDescriptor as $n) {
            $n->toXML($e);
        }

        if ($this->AffiliationDescriptor !== null) {
            $this->AffiliationDescriptor->toXML($e);
        }

        if ($this->Organization !== null) {
            $this->Organization->toXML($e);
        }

        foreach ($this->ContactPerson as $cp) {
            $cp->toXML($e);
        }

        foreach ($this->AdditionalMetadataLocation as $n) {
            $n->toXML($e);
        }

        /** @var \DOMElement $child */
        $child = $e->firstChild;
        $this->signElement($e, $child);

        return $e;
    }
}
