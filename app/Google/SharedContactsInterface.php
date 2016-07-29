<?php


namespace GSharedContacts\Google;


use DOMDocument;
use GSharedContacts\Feed\Entry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface SharedContactsInterface
 *
 * @package GSharedContacts\Google
 */
interface SharedContactsInterface
{

    /**
     * @return mixed
     */
    public function all();

    /**
     * @param $code
     * @param $arr
     *
     * @return mixed
     */
    public function buildEntryFromArray($code, $arr);

    /**
     * @param DomDocument $xml
     *
     * @return mixed
     */
    public function create(DomDocument $xml);

    /**
     * @param Entry                 $contact
     * @param                       $code
     *
     * @return mixed
     */
    public function delete(Entry $contact, $code);

    /**
     * @param array $batch
     *
     * @return mixed
     */
    public function deleteBatch(array $batch);

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getContact($code);

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

    /**
     * @param array $batch
     *
     * @return mixed
     */
    public function insertBatch(array $batch);

    /**
     * @return mixed
     */
    public function logout();

    /**
     * @param DomDocument  $xml
     * @param              $code
     *
     * @return mixed
     */
    public function update(DomDocument $xml, $code);

    /**
     * @param              $code
     * @param UploadedFile $photo
     *
     * @return mixed
     */
    public function uploadPhoto($code, UploadedFile $photo);

} 