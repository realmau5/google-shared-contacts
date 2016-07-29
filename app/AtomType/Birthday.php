<?php

namespace GSharedContacts\AtomType;

use DOMAttr;
use DOMDocument;
use DOMElement;
use Log;

/**
 * Class Birthday
 *
 * @package GSharedContacts\AtomType
 */
class Birthday
{
    public static $GD = 'http://schemas.google.com/contact/2008';

    public $when;


    /**
     * @param DomDocument $dom
     *
     * @return Birthday|null
     */
    public static function parseFromDomDocument(DomDocument $doc)
    {
        $children = $doc->getElementsByTagNameNS(self::$GD, 'birthday');
        $birthday = new self;
        // always just one:
        /** @var DOMElement $child */
        foreach ($children as $child) {
            $attributes = $child->attributes;
            /** @var DOMAttr $attr */
            foreach ($attributes as $attr) {
                if ($attr->nodeName == 'when') {
                    $birthday->setWhen($attr->nodeValue);
                }
            }
        }
        return $birthday;
    }

    /**
     * @param $array
     *
     * @return Birthday|null
     */
    public static function parseFromArray($array)
    {

        if (isset($array['birthday'])) {
            $birthday = new self;
            $birthday->setWhen($array['birthday']);
            return $birthday;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * @param mixed $when
     */
    public function setWhen($when)
    {
        $this->when = $when;
    }


}