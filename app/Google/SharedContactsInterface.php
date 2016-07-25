<?php


namespace GContacts\Google;


use DOMDocument;
use GContacts\Feed\Entry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface SharedContactsInterface
 *
 * @package GContacts\Google
 */
interface SharedContactsInterface
{

    public function all();

    public function logout();

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getContact($code);

    /**
     * @param $code
     * @param $arr
     *
     * @return mixed
     */
    public function buildEntryFromArray($code, $arr);

    /**
     * @param              $code
     * @param UploadedFile $photo
     *
     * @return mixed
     */
    public function uploadPhoto($code, UploadedFile $photo);

    /**
     * @param DomDocument  $xml
     * @param              $code
     *
     * @return mixed
     */
    public function update(DomDocument $xml, $code);

    /**
     * @param Entry                 $contact
     * @param                       $code
     *
     * @return mixed
     */
    public function delete(Entry $contact, $code);

    /**
     * @param DomDocument $xml
     *
     * @return mixed
     */
    public function create(DomDocument $xml);

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getPhoto($code);

    /**
     * @param $tempToken
     *
     * @return mixed
     */
    public function getToken($tempToken);

} 