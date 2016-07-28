<?php


namespace GSharedContacts\AtomType;

use DomDocument;
use DomNode;

/**
 * Class Link
 *
 * @package GSharedContacts\AtomType
 */
class Link extends DefaultAtomType
{
    public static $supported = ['rel', 'type', 'text', 'href', 'etag'];
    public        $rel;
    public        $type;
    public        $href;
    public        $etag;
    public        $text;

    /**
     * @param DomDocument $DOM
     *
     * @return array
     */
    public static function parseFromDomDocument(DomDocument $DOM)
    {
        $children = $DOM->getElementsByTagName('link');
        $links    = [];
        /** @var $child DomNode */
        foreach ($children as $child) {
            $link = new self;

            // rel from XML:
            foreach (self::$supported as $field) {
                $content = $child->attributes->getNamedItem($field);
                if (!is_null($content)) {
                    $fn = 'set' . ucfirst($field);
                    $link->$fn($content->nodeValue);
                }
            }
            $links[] = $link;
        }
        return $links;
    }

    /**
     * @param DomDocument $dom
     *
     * @return null
     */
    public function parseToDomNode(DomDocument $dom)
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * @param mixed $etag
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
    }

    /**
     * @return mixed
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param mixed $href
     */
    public function setHref($href)
    {
        $this->href = $href;
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
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


}