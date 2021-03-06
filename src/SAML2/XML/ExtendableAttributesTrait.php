<?php

declare(strict_types=1);

namespace SAML2\XML;

use DOMAttr;
use DOMElement;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * Trait for elements that can have arbitrary namespaced attributes.
 *
 * @package simplesamlphp/saml2
 */
trait ExtendableAttributesTrait
{
    /**
     * Extra (namespace qualified) attributes.
     *
     * @var array
     */
    protected $namespacedAttributes = [];


    /**
     * Check if a namespace-qualified attribute exists.
     *
     * @param string $namespaceURI The namespace URI.
     * @param string $localName The local name.
     * @return bool true if the attribute exists, false if not.
     */
    public function hasAttributeNS(string $namespaceURI, string $localName): bool
    {
        return isset($this->namespacedAttributes['{' . $namespaceURI . '}' . $localName]);
    }


    /**
     * Get a namespace-qualified attribute.
     *
     * @param string $namespaceURI The namespace URI.
     * @param string $localName The local name.
     * @return string|null The value of the attribute, or null if the attribute does not exist.
     */
    public function getAttributeNS(string $namespaceURI, string $localName): ?string
    {
        return isset($this->namespacedAttributes['{' . $namespaceURI . '}' . $localName])
            ? $this->namespacedAttributes['{' . $namespaceURI . '}' . $localName]['value']
            : null;
    }


    /**
     * Get the namespaced attributes in this endpoint.
     *
     * @return array
     */
    public function getAttributesNS(): array
    {
        return $this->namespacedAttributes;
    }


    /**
     * Parse an XML document representing an EndpointType and get the namespaced attributes.
     *
     * @param \DOMElement $xml
     *
     * @return array|null
     */
    protected static function getAttributesNSFromXML(DOMElement $xml): ?array
    {
        $attributes = [];

        foreach ($xml->attributes as $a) {
            if ($a->namespaceURI === null) {
                // Not namespace-qualified -- skip.
                continue;
            }
            $attributes[] = $a;
        }

        return $attributes;
    }


    /**
     * Get a namespace-qualified attribute.
     *
     * @param string $namespaceURI  The namespace URI.
     * @param string $qualifiedName The local name.
     * @param string $value The attribute value.
     * @throws \InvalidArgumentException
     */
    protected function setAttributeNS(string $namespaceURI, string $qualifiedName, string $value): void
    {
        $name = explode(':', $qualifiedName, 2);
        if (count($name) < 2) {
            throw new InvalidArgumentException('Not a qualified name.');
        }
        $localName = $name[1];

        $this->namespacedAttributes['{' . $namespaceURI . '}' . $localName] = [
            'qualifiedName' => $qualifiedName,
            'namespaceURI' => $namespaceURI,
            'value' => $value,
        ];
    }


    /**
     * @param \DOMAttr[] $attributes
     *
     * @throws \InvalidArgumentException
     */
    protected function setAttributesNS(?array $attributes): void
    {
        if ($attributes === null) {
            return;
        }

        Assert::allIsInstanceOf($attributes, DOMAttr::class);

        /** @var DOMAttr $attribute */
        foreach ($attributes as $attribute) {
            $this->setAttributeNS($attribute->namespaceURI, $attribute->nodeName, $attribute->value);
        }
    }
}
