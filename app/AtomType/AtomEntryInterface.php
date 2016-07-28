<?php


namespace GSharedContacts\AtomType;

use DomDocument;

/**
 * Interface AtomEntryInterface
 *
 * @package GSharedContacts\AtomType
 */
interface AtomEntryInterface
{
    /**
     * @param DomDocument $doc
     *
     * @return mixed
     */
    public static function parseFromDomDocument(DomDocument $doc);

    /**
     * @param $array
     *
     * @return mixed
     */
    public static function parseFromArray($array);

    /**
     * @param DomDocument $dom
     *
     * @return mixed
     */
    public function parseToDomNode(DomDocument $dom);


} 