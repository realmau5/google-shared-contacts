<?php
namespace GContacts\AtomType;

use DomDocument;
use DomElement;
use DomNode;

/**
 * Class Phonenumber
 *
 * @package GContacts\AtomType
 */
class Phonenumber extends DefaultAtomType implements AtomEntryInterface
{
    public    $label;
    public    $rel;
    public    $primary;
    public    $number;
    public    $uri;
    protected $rels;
    protected $node;

    /**
     *
     */
    public function __construct()
    {
        $this->rels = self::getDefaultRels();
    }

    /**
     * @return array
     */
    public static function getDefaultRels()
    {
        return [
            'http://schemas.google.com/g/2005#assistant'    => 'Assistant',
            'http://schemas.google.com/g/2005#callback'     => 'Callback',
            'http://schemas.google.com/g/2005#car'          => 'Car',
            'http://schemas.google.com/g/2005#company_main' => 'Company (main)',
            'http://schemas.google.com/g/2005#fax'          => 'Fax',
            'http://schemas.google.com/g/2005#home'         => 'Home',
            'http://schemas.google.com/g/2005#home_fax'     => 'Home Fax',
            'http://schemas.google.com/g/2005#isdn'         => 'ISDN',
            'http://schemas.google.com/g/2005#main'         => 'Main',
            'http://schemas.google.com/g/2005#mobile'       => 'Mobile',
            'http://schemas.google.com/g/2005#other'        => 'Other',
            'http://schemas.google.com/g/2005#other_fax'    => 'Other Fax',
            'http://schemas.google.com/g/2005#pager'        => 'Pager',
            'http://schemas.google.com/g/2005#radio'        => 'Radio',
            'http://schemas.google.com/g/2005#telex'        => 'Telex',
            'http://schemas.google.com/g/2005#tty_tdd'      => 'TTY/TDD',
            'http://schemas.google.com/g/2005#work'         => 'Work',
            'http://schemas.google.com/g/2005#work_fax'     => 'Work Fax',
            'http://schemas.google.com/g/2005#work_mobile'  => 'Work Mobile',
            'http://schemas.google.com/g/2005#work_pager'   => 'Work Pager',
        ];
    }

    /**
     * @param $array
     *
     * @return Phonenumber
     */
    public static function parseFromArray($array)
    {
        $phone = new self;
        $phone->setLabel($array['label']);
        $phone->setNumber($array['number']);
        $phone->setPrimary(isset($array['primary']) ? $array['primary'] : false);
        $phone->setRel($array['rel']);
        //$phone->setUri($array['uri']);
        return $phone;
    }

    /**
     * @param DomDocument $doc
     *
     * @return array
     */
    public static function parseFromDomDocument(DomDocument $doc)
    {
        $list         = $doc->getElementsByTagNameNS(self::$GD, 'phoneNumber');
        $phoneNumbers = [];
        if ($list->length > 0) {
            /** @var $node \DomNode */
            foreach ($list as $node) {
                $phoneNumber = new self;
                $var         = trim($node->nodeValue);
                $phoneNumber->setNumber($var);

                $var = $node->attributes->getNamedItem('label');
                $phoneNumber->setLabel($var ? $var->nodeValue : null);

                $var = $node->attributes->getNamedItem('uri');
                $phoneNumber->setUri($var ? $var->nodeValue : null);

                $var = $node->attributes->getNamedItem('rel');
                $phoneNumber->setRel($var ? $phoneNumber->rels[$var->nodeValue] : null);

                $var = $node->attributes->getNamedItem('primary');
                $phoneNumber->setPrimary($var ? $var->nodeValue : null);

                $phoneNumbers[] = $phoneNumber;
            }
        }
        return $phoneNumbers;
    }

    /**
     * @param DomDocument $dom
     *
     * @return DOMElement
     */
    public function parseToDomNode(DomDocument $dom)
    {
        $phoneNumber = $dom->createElement('gd:phoneNumber', $this->getNumber());

        if ($this->isPrimary()) {
            $phoneNumber->setAttribute('primary', 'true');
        }
        if (strlen($this->getLabel()) > 0) {
            $phoneNumber->setAttribute('label', $this->getLabel());
        } else {
            foreach (self::getDefaultRels() as $key => $rel) {
                if ($rel == $this->getRel()) {
                    $phoneNumber->setAttribute('rel', $key);
                }
            }
        }
        if (strlen($this->getUri()) > 0) {
            $phoneNumber->setAttribute('uri', $this->getUri());
        }

        return $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
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