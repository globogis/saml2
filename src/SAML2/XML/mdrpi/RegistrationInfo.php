<?php

declare(strict_types=1);

namespace SAML2\XML\mdrpi;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class for handling the mdrpi:RegistrationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package SimpleSAMLphp
 */
final class RegistrationInfo extends AbstractMdrpiElement
{
    /**
     * The identifier of the metadata registration authority.
     *
     * @var string
     */
    protected $registrationAuthority;

    /**
     * The registration timestamp for the metadata, as a UNIX timestamp.
     *
     * @var int|null
     */
    protected $registrationInstant = null;

    /**
     * Link to registration policy for this metadata.
     *
     * This is an associative array with language=>URL.
     *
     * @var array|null
     */
    protected $RegistrationPolicy = null;


    /**
     * Create/parse a mdrpi:RegistrationInfo element.
     *
     * @param string $registrationAuthority
     * @param int|null $registrationInstant
     * @param array|null $RegistrationPolicy
     */
    public function __construct(
        string $registrationAuthority,
        int $registrationInstant = null,
        array $RegistrationPolicy = null
    ) {
        $this->setRegistrationAuthority($registrationAuthority);
        $this->setRegistrationInstant($registrationInstant);
        $this->setRegistrationPolicy($RegistrationPolicy);
    }


    /**
     * Collect the value of the RegistrationAuthority property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getRegistrationAuthority(): string
    {
        return $this->registrationAuthority;
    }


    /**
     * Set the value of the registrationAuthority property
     *
     * @param string $registrationAuthority
     * @return void
     */
    private function setRegistrationAuthority(string $registrationAuthority): void
    {
        $this->registrationAuthority = $registrationAuthority;
    }


    /**
     * Collect the value of the registrationInstant property
     *
     * @return int|null
     */
    public function getRegistrationInstant(): ?int
    {
        return $this->registrationInstant;
    }


    /**
     * Set the value of the registrationInstant property
     *
     * @param int|null $registrationInstant
     * @return void
     */
    private function setRegistrationInstant(?int $registrationInstant): void
    {
        $this->registrationInstant = $registrationInstant;
    }


    /**
     * Collect the value of the RegistrationPolicy property
     *
     * @return array|null
     */
    public function getRegistrationPolicy(): ?array
    {
        return $this->RegistrationPolicy;
    }


    /**
     * Set the value of the RegistrationPolicy property
     *
     * @param array|null $registrationPolicy
     * @return void
     */
    private function setRegistrationPolicy(?array $registrationPolicy): void
    {
        $this->RegistrationPolicy = $registrationPolicy;
    }


    /**
     * Convert XML into a RegistrationInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RegistrationInfo');
        Assert::same($xml->namespaceURI, RegistrationInfo::NS);

        if (!$xml->hasAttribute('registrationAuthority')) {
            throw new \Exception(
                'Missing required attribute "registrationAuthority" in mdrpi:RegistrationInfo element.'
            );
        }

        $registrationAuthority = $xml->getAttribute('registrationAuthority');
        $registrationInstant = $xml->hasAttribute('registrationInstant')
            ? Utils::xsDateTimeToTimestamp($xml->getAttribute('registrationInstant'))
            : null;
        $RegistrationPolicy = Utils::extractLocalizedStrings($xml, RegistrationInfo::NS, 'RegistrationPolicy');

        return new self($registrationAuthority, $registrationInstant, empty($RegistrationPolicy) ? null : $RegistrationPolicy);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('registrationAuthority', $this->registrationAuthority);

        if ($this->registrationInstant !== null) {
            $e->setAttribute('registrationInstant', gmdate('Y-m-d\TH:i:s\Z', $this->registrationInstant));
        }

        if (!empty($this->RegistrationPolicy)) {
            Utils::addStrings($e, RegistrationInfo::NS, 'mdrpi:RegistrationPolicy', true, $this->RegistrationPolicy);
        }

        return $e;
    }
}
