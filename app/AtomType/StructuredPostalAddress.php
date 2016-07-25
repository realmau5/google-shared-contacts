<?php


namespace GContacts\AtomType;

use DomDocument;
use DomNode;

/**
 * Class StructuredPostalAddress
 *
 * @package GContacts\AtomType
 */
class StructuredPostalAddress extends DefaultAtomType implements AtomEntryInterface
{
    public $rel;
    public $mailClass;
    public $usage;

    // attributes:
    public $label;
    public $primary;
    public $agent;
    public $housename;
    public $street; #5

    // fields:
    public    $pobox;
    public    $neighborhood;
    public    $city;
    public    $subregion;
    public    $region;
    public    $postcode;
    public    $country;
    public    $formattedAddress;
    protected $node;
    protected $rels;
    protected $mailclasses; #11

    /**
     *
     */
    public function __construct()
    {
        $this->rels        = self::getDefaultRels();
        $this->mailclasses = self::getDefaultMailClasses();
        $this->usages      = self::getDefaultUsages();

    }

    /**
     * @return array
     */
    public static function getDefaultRels()
    {
        return ['http://schemas.google.com/g/2005#work'  => 'Work',
                'http://schemas.google.com/g/2005#home'  => 'Home',
                'http://schemas.google.com/g/2005#other' => 'Other',
                ''                                       => null
        ];

    }

    /**
     * @return array
     */
    public static function getDefaultMailClasses()
    {
        return [
            'http://schemas.google.com/g/2005#both'    => 'Both',
            'http://schemas.google.com/g/2005#letters' => 'Letters',
            'http://schemas.google.com/g/2005#parcels' => 'Parcels',
            'http://schemas.google.com/g/2005#neither' => 'Neither',
            ''                                         => null
        ];
    }

    /**
     * @return array
     */
    public static function getDefaultUsages()
    {
        return [
            'http://schemas.google.com/g/2005#general' => 'General',
            'http://schemas.google.com/g/2005#local'   => 'Local',
            ''                                         => null
        ];
    }

    /**
     * @param $array
     *
     * @return StructuredPostalAddress
     */
    public static function parseFromArray($array)
    {
        $address = new self;
        $address->setRel($array['rel']);
        $address->setMailClass($array['mailclass']);
        $address->setUsage($array['usage']);
        $address->setLabel($array['label']);
        $address->setPrimary(isset($array['primary']) ? $array['primary'] : false);
        $address->setAgent($array['agent']);
        $address->setHousename($array['housename']);
        $address->setStreet($array['street']);
        $address->setPobox($array['pobox']);
        $address->setNeighborhood($array['neighborhood']);
        $address->setCity($array['city']);
        $address->setSubregion($array['subregion']);
        $address->setRegion($array['region']);
        $address->setPostcode($array['postcode']);
        $address->setCountry($array['country']);
        $address->generateFormattedAddress();
        return $address;
    }

    public function generateFormattedAddress()
    {
        $arr = [
            $this->getHousename(),
            $this->getAgent(),
            $this->getStreet(),
            $this->getPobox(),
            $this->getNeighborhood(),
            $this->getPostcode(),
            $this->getCity(),
            $this->getSubregion(),
            $this->getRegion(),
            $this->getCountry(),
        ];
        $arr = array_filter($arr);
        $this->setFormattedAddress(join(' ', $arr));
    }

    /**
     * @return mixed
     */
    public function getHousename()
    {
        return $this->housename;
    }

    /**
     * @param mixed $housename
     */
    public function setHousename($housename)
    {
        $this->housename = $housename;
    }

    /**
     * @return mixed
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param mixed $agent
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getPobox()
    {
        return $this->pobox;
    }

    /**
     * @param mixed $pobox
     */
    public function setPobox($pobox)
    {
        $this->pobox = $pobox;
    }

    /**
     * @return mixed
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * @param mixed $neighborhood
     */
    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param mixed $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getSubregion()
    {
        return $this->subregion;
    }

    /**
     * @param mixed $subregion
     */
    public function setSubregion($subregion)
    {
        $this->subregion = $subregion;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @param DomDocument $doc
     *
     * @return array
     */
    public static function parseFromDomDocument(DomDocument $doc)
    {
        $list      = $doc->getElementsByTagNameNS(self::$GD, 'structuredPostalAddress');
        $addresses = [];
        if ($list->length > 0) {
            /** @var $node \DomNode */
            foreach ($list as $node) {
                $address = new self;
                // attributes:
                $var = $node->attributes->getNamedItem('primary');
                $address->setPrimary($var ? $var->nodeValue : null);

                $var = $node->attributes->getNamedItem('label');
                $address->setLabel($var ? $var->nodeValue : null);

                $var = $node->attributes->getNamedItem('rel');
                $address->setRel($var ? $address->rels[$var->nodeValue] : null);

                $var = $node->attributes->getNamedItem('mailClass');
                $address->setMailClass($var ? $address->mailclasses[$var->nodeValue] : null);

                $var = $node->attributes->getNamedItem('usage');
                $address->setUsage($var ? $address->usages[$var->nodeValue] : null);

                // fields:
                $fields = ['agent', 'housename', 'street', 'pobox', 'neighborhood', 'city', 'subregion', 'region',
                           'postcode', 'country', 'formattedAddress',];
                /** @var $childNode \DomNode */
                foreach ($node->childNodes as $childNode) {
                    $nodeName = str_replace('gd:', '', $childNode->nodeName);
                    if (in_array($nodeName, $fields)) {
                        $fn = 'set' . ucFirst($nodeName);
                        $address->$fn($childNode->nodeValue);
                    }
                }

                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    /**
     * @param DomDocument $dom
     *
     * @return DOMElement
     */
    public function parseToDomNode(DomDocument $dom)
    {
        $address = $dom->createElement('gd:structuredPostalAddress');
        // attrs:
        foreach (self::getDefaultRels() as $key => $rel) {
            if ($rel == $this->getRel()) {
                $address->setAttribute('rel', $key);
            }
        }
        foreach (self::getDefaultMailClasses() as $key => $mc) {
            if ($mc == $this->getMailClass()) {
                $address->setAttribute('mailClass', $key);
            }
        }
        foreach (self::getDefaultUsages() as $key => $us) {
            if ($us == $this->getUsage()) {
                $address->setAttribute('usage', $key);
            }
        }

        if ($this->isPrimary()) {
            $address->setAttribute('primary', 'true');
        }
        $address->setAttribute('label', $this->getLabel());

        // fields:
        $fields = ['agent', 'housename', 'street', 'pobox', 'neighborhood', 'city', 'subregion', 'region',
                   'postcode', 'country', 'formattedAddress',];
        foreach ($fields as $field) {
            $fn = 'get' . ucfirst($field);
            if (method_exists($this, $fn) && strlen($this->$fn()) > 0) {
                $address->appendChild($dom->createElement('gd:' . $field, $this->$fn()));
            }
        }
        return $address;

    }

    /**
     * @return mixed
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @param mixed $rel
     */
    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    /**
     * @return mixed
     */
    public function getMailClass()
    {
        return $this->mailClass;
    }

    /**
     * @param mixed $mailClass
     */
    public function setMailClass($mailClass)
    {
        $this->mailClass = $mailClass;
    }

    /**
     * @return mixed
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @param mixed $usage
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return $this->primary == 'true' || $this->primary == '1';
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * @param mixed $formattedAddress
     */
    public function setFormattedAddress($formattedAddress)
    {
        $this->formattedAddress = $formattedAddress;
    }

    /**
     * @return mixed
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @param mixed $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }


}