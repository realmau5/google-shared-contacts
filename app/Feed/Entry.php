<?php


namespace GSharedContacts\Feed;

use DomDocument;
use GSharedContacts\AtomType\Birthday;
use GSharedContacts\AtomType\Category;
use GSharedContacts\AtomType\Name;

/**
 * Class Entry
 *
 * @package GSharedContacts\Feed
 */
class Entry
{
    public $id;
    public $updated;

    // new holders for fields:
    public    $title;
    public    $content;
    public    $shortID;
    public    $etag;
    public    $link                    = [];
    public    $category                = [];
    public    $email                   = [];
    public    $im                      = [];
    public    $phoneNumber             = [];
    public    $name                    = [];
    public    $structuredPostalAddress = [];
    public    $organization            = [];
    public    $birthday;
    protected $_xml;
    /** @var DomDocument */
    protected $_dom;

    /**
     * @param null $xml
     */
    public function __construct($xml = null)
    {
        if (!is_null($xml)) {
            $this->_xml               = $xml;
            $this->_dom               = new DomDocument();
            $this->_dom->formatOutput = true;
            $this->_dom->loadXML($this->_xml);
        }
    }

    /**
     * @return DomDocument
     */
    public function getNode()
    {
        return $this->_dom;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param array $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param array $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getIm()
    {
        return $this->im;
    }

    /**
     * @param array $im
     */
    public function setIm($im)
    {
        $this->im = $im;
    }

    /**
     * @return array
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param array $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return array
     */
    public function getStructuredPostalAddress()
    {
        return $this->structuredPostalAddress;
    }

    /**
     * @param array $structuredPostalAddress
     */
    public function setStructuredPostalAddress($structuredPostalAddress)
    {
        $this->structuredPostalAddress = $structuredPostalAddress;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getShortID()
    {
        return $this->shortID;
    }

    /**
     * @param mixed $shortID
     */
    public function setShortID($shortID)
    {
        $this->shortID = $shortID;
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
     * @return array
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param array $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return DomDocument
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * @param DomDocument $dom
     */
    public function setDom($dom)
    {
        $this->_dom = $dom;
    }

    /**
     * @return Birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param Birthday $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }




} 