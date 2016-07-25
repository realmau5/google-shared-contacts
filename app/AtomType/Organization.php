<?php
namespace GContacts\AtomType;

use DomDocument;

/**
 * Class Organization
 *
 * @package GContacts\AtomType
 */
class Organization extends DefaultAtomType implements AtomEntryInterface
{
    public $rels;

    public $label;
    public $orgDepartment;
    public $orgJobDescription;
    public $orgName;
    public $orgSymbol;
    public $orgTitle;
    public $primary;
    public $rel;
    public $where;

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
            'http://schemas.google.com/g/2005#other' => 'Other',
            'http://schemas.google.com/g/2005#work'  => 'Work',
        ];
    }

    /**
     * @param DomDocument $doc
     *
     * @return array
     */
    public static function parseFromDomDocument(DomDocument $doc)
    {
        $list          = $doc->getElementsByTagNameNS(self::$GD, 'organization');
        $organizations = [];
        if ($list->length > 0) {
            /** @var $node \DomNode */
            foreach ($list as $node) {
                $organization = new self;

                $var = $node->attributes->getNamedItem('label');
                $organization->setLabel($var ? $var->nodeValue : null);

                $var = $node->attributes->getNamedItem('primary');
                $organization->setPrimary($var ? $var->nodeValue : null);

                $var = $node->attributes->getNamedItem('rel');
                $organization->setRel($var ? $organization->rels[$var->nodeValue] : null);

                $fields = ['orgDepartment', 'orgJobDescription', 'orgName', 'orgSymbol', 'orgTitle'];
                /** @var $childNode \DomNode */
                foreach ($node->childNodes as $childNode) {
                    $nodeName = str_replace('gd:', '', $childNode->nodeName);
                    if (in_array($nodeName, $fields)) {
                        $fn = 'set' . ucfirst($nodeName);
                        $organization->$fn($childNode->nodeValue);
                    }
                    if ($nodeName == 'where') {
                        $organization->setWhere($childNode->attributes->getNamedItem('valueString')->nodeValue);
                    }
                }


                $organizations[] = $organization;
            }
        }
        return $organizations;


    }

    /**
     * @param $array
     *
     * @return Organization
     */
    public static function parseFromArray($array)
    {
        $organization = new self;
        $organization->setLabel($array['label']);
        $organization->setOrgDepartment($array['orgdepartment']);
        $organization->setOrgJobDescription($array['orgjobdescription']);
        $organization->setOrgDepartment($array['orgdepartment']);
        $organization->setOrgName($array['orgname']);
        $organization->setOrgSymbol($array['orgsymbol']);
        $organization->setRel($array['rel']);
        $organization->setOrgTitle($array['orgtitle']);
        $organization->setPrimary(isset($array['primary']) ? $array['primary'] : false);
        $organization->setWhere($array['where']);
        return $organization;


    }

    /**
     * @param DomDocument $dom
     *
     * @return \DOMElement
     */
    public function parseToDomNode(DomDocument $dom)
    {
        $org = $dom->createElement('gd:organization');
        // attrs:
        if ($this->getLabel() == '') {
            foreach (self::getDefaultRels() as $key => $rel) {
                if ($rel == $this->getRel()) {
                    $org->setAttribute('rel', $key);
                }
            }
        } else {
            $org->setAttribute('label', $this->getLabel());
        }
        if ($this->isPrimary()) {
            $org->setAttribute('primary', 'true');
        }


        // fields:
        $fields = ['orgDepartment', 'orgJobDescription', 'orgName', 'orgSymbol', 'orgTitle'];
        foreach ($fields as $field) {
            $fn = 'get' . ucfirst($field);
            if (method_exists($this, $fn) && strlen($this->$fn()) > 0) {
                $org->appendChild($dom->createElement('gd:' . $field, $this->$fn()));
            }
        }
        $whereNode = $dom->createElement('gd:where');
        $whereNode->setAttribute('valueString', $this->getWhere());
        $org->appendChild($whereNode);
        return $org;
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
     * @return bool
     */
    public function isPrimary()
    {
        return $this->primary == 'true' || $this->primary == '1';
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param mixed $where
     */
    public function setWhere($where)
    {
        $this->where = $where;
    }

    /**
     * @return mixed
     */
    public function getOrgDepartment()
    {
        return $this->orgDepartment;
    }

    /**
     * @param mixed $orgDepartment
     */
    public function setOrgDepartment($orgDepartment)
    {
        $this->orgDepartment = $orgDepartment;
    }

    /**
     * @return mixed
     */
    public function getOrgJobDescription()
    {
        return $this->orgJobDescription;
    }

    /**
     * @param mixed $orgJobDescription
     */
    public function setOrgJobDescription($orgJobDescription)
    {
        $this->orgJobDescription = $orgJobDescription;
    }

    /**
     * @return mixed
     */
    public function getOrgName()
    {
        return $this->orgName;
    }

    /**
     * @param mixed $orgName
     */
    public function setOrgName($orgName)
    {
        $this->orgName = $orgName;
    }

    /**
     * @return mixed
     */
    public function getOrgSymbol()
    {
        return $this->orgSymbol;
    }

    /**
     * @param mixed $orgSymbol
     */
    public function setOrgSymbol($orgSymbol)
    {
        $this->orgSymbol = $orgSymbol;
    }

    /**
     * @return mixed
     */
    public function getOrgTitle()
    {
        return $this->orgTitle;
    }

    /**
     * @param mixed $orgTitle
     */
    public function setOrgTitle($orgTitle)
    {
        $this->orgTitle = $orgTitle;
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