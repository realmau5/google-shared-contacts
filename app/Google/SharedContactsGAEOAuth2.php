<?php
namespace GSharedContacts\Google;

use DOMDocument;
use Exception;
use GSharedContacts\Feed\Entry;
use GSharedContacts\Feed\EntryParser;
use GSharedContacts\Support\Variables;
use Log;
use Requests;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zend\Feed\Reader\Reader;

/**
 * Class SharedContactsGAEOAuth2
 *
 * @package GSharedContacts\Google
 */
class SharedContactsGAEOAuth2 implements SharedContactsInterface
{

    /**
     * @return array
     */
    public function all()
    {
        $return       = [];
        $moreContacts = true;
        $currentIndex = 1;
        Log::debug('Start at index ' . $currentIndex);
        $loopCounter = 0;
        while ($moreContacts) {
            Log::debug('Loopcounter: ' . $loopCounter);
            $rawBody = $this->_getAllContacts($currentIndex);
            $feed    = Reader::importString($rawBody);
            Log::debug('Entries found in set', ['count' => $feed->count()]);
            // and add them to the return list.
            if ($feed->count() > 0) {
                foreach ($feed as $entry) {
                    $return[] = EntryParser::parseFromXML($entry->saveXml());
                }
            }

            // we figure out if there is more to come:
            $DOMDocument = new DOMDocument();
            $DOMDocument->loadXML($rawBody);
            $nextLink     = false;
            $moreContacts = false;
            /** @var $child \DomNode */
            foreach ($DOMDocument->getElementsByTagName('link') as $child) {
                $rel = $child->attributes->getNamedItem('rel')->nodeValue;
                if ($rel == 'next') {
                    $moreContacts = true;
                    $currentIndex += $feed->count();
                }
            }
            $loopCounter++;
        }

        return $return;

    }

    /**
     * @param $code
     * @param $arr
     *
     * @return mixed
     */
    public function buildEntryFromArray($code, $arr)
    {
        return Entry::buildEntryFromArray($code, $arr);
    }

    /**
     * @param DOMDocument $xml
     *
     * @return bool|string
     */
    public function create(DOMDocument $xml)
    {
        $URL   = 'https://www.google.com/m8/feeds/contacts/' . session('hd') . '/full';
        $token = session('access_token');

        $headers
              = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\n";
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => $headers,
                'content' => $xml->saveXML(),
            ],
        ];

        Log::debug($xml->saveXML());

        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'Error while retrieving contact.';
        }

        return true;
    }

    /**
     * @param Entry                 $contact
     * @param                       $code
     *
     * @return bool|string
     */
    public function delete(Entry $contact, $code)
    {
        $token   = session('access_token');
        $URL     = 'https://www.google.com/m8/feeds/contacts/' . session('hd') . '/full/' . $code;
        $headers = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\nIf-Match: "
                   . $contact->getEtag() . "\r\n";

        $opts    = [
            'http' => [
                'method' => 'DELETE',
                'header' => $headers,
            ],
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'Error while retreiving contact.';
        }

        return true;
    }

    /**
     * @param array $batch
     *
     * @return mixed
     */
    public function deleteBatch(array $batch)
    {

        $completeSet = array_chunk($batch, 100);

        foreach ($completeSet as $newBatch) {

            $batchFeed               = new DOMDocument('1.0', 'UTF-8');
            $batchFeed->formatOutput = true;
            // create ROOT
            $feed = $batchFeed->createElement('feed');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'http://www.w3.org/2005/Atom');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gContact', 'http://schemas.google.com/contact/2008');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:batch', 'http://schemas.google.com/gdata/batch');

            // create category thing:
            $category = $batchFeed->createElement('category');
            $category->setAttribute('scheme', 'http://schemas.google.com/g/2005#kind');
            $category->setAttribute('term', 'http://schemas.google.com/g/2008#contact');
            $feed->appendChild($category);
            $batchFeed->appendChild($feed);

            $index = 1;
            /** @var DOMDocument $entry */
            foreach ($newBatch as $entry) {

                $code = $entry['code'];
                $etag = $entry['etag'];
                $node = $batchFeed->createElement('entry');
                if (strlen($etag) > 0) {
                    $node->setAttribute('gd:etag', $etag);
                }


                // add an edit-link:
                $link = $batchFeed->createElement('link');
                $link->setAttribute('rel', 'edit');
                $link->setAttribute('type', 'application/atom+xml');
                $link->setAttribute('href', sprintf('https://www.google.com/m8/feeds/contacts/%s/full/%s', session('hd'), $code));
                $node->appendChild($link);

                // add id thing:
                $id = $batchFeed->createElement('id', sprintf('http://www.google.com/m8/feeds/contacts/%s/base/%s', session('hd'), $code));
                $node->appendChild($id);

                // add batch stuff.
                $batchId = $batchFeed->createElement('batch:id', $index);
                $batchOp = $batchFeed->createElement('batch:operation');
                $batchOp->setAttribute('type', 'delete');
                $node->appendChild($batchId);
                $node->appendChild($batchOp);
                $feed->appendChild($node);
                //                $index++;
            }

            Log::debug('Mass delete XML: ' . $batchFeed->saveXML());

            // send to Google:
            $URL     = Variables::batchUri();
            $headers = Variables::getAuthHeadersForUpdate();

            $result = Requests::post($URL, $headers, $batchFeed->saveXML());
            Log::debug('Mass delete status code: ' . $result->status_code);
            Log::debug('Mass delete body: ' . $result->body);
        }

    }

    /**
     * @param $code
     *
     * @return \GSharedContacts\Feed\Entry|string
     */
    public function getContact($code)
    {
        $URL     = 'https://www.google.com/m8/feeds/contacts/' . session('hd') . '/full/' . $code;
        $token   = session('access_token');
        $headers
                 = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: application/x-www-form-urlencoded\r\nGData-Version: 3.0\r\n";
        $opts    = [
            'http' => [
                'method' => 'GET',
                'header' => $headers,
            ],
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'Error while retreiving contact.';
        }
        Log::debug('Get contact: ' . $result);

        return EntryParser::parseFromXML($result);
    }

    /**
     * @param $code
     *
     * @return null|string
     */
    public function getPhoto($code)
    {
        $entry = $this->getContact($code);
        /** @var $link \GSharedContacts\AtomType\Link */
        foreach ($entry->getLink() as $link) {
            if ($link->getRel() == 'http://schemas.google.com/contacts/2008/rel#photo') {
                // get photo from Google:
                $URL     = $link->getHref();
                $token   = session('access_token');
                $headers
                         = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\n";
                $opts    = [
                    'http' => [
                        'method' => 'GET',
                        'header' => $headers,
                    ],
                ];
                $context = stream_context_create($opts);
                $result  = @file_get_contents($URL, false, $context);
                if ($result === false) {
                    return null;
                }

                return $result;
            }
        }

        return null;
    }

    /**
     * @param $tempToken
     *
     * @return mixed|string
     */
    public function getToken($tempToken)
    {
        $URL = 'https://www.google.com/accounts/AuthSubSessionToken';

        $context = [
            'http' => [
                'method'  => 'GET',
                'header'  =>
                    'Authorization: AuthSub token="' . $tempToken . '"' . "\r\n" .
                    'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
                    'User-Agent: GSharedContacts/0.1' . "\r\n",
                'content' => null,
            ],
        ];
        $context = stream_context_create($context);
        $result  = @file_get_contents($URL, null, $context);
        if ($result === false) {
            return 'Error while getting token.';
        }
        $token = str_replace('Token=', '', $result);

        return $token;
    }

    /**
     * @param array $batch
     *
     * @return mixed
     */
    public function insertBatch(array $batch)
    {
        $completeSet = array_chunk($batch, 100);

        foreach ($completeSet as $newBatch) {

            $batchFeed               = new DOMDocument('1.0', 'UTF-8');
            $batchFeed->formatOutput = true;
            // create ROOT
            $feed = $batchFeed->createElement('feed');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'http://www.w3.org/2005/Atom');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gContact', 'http://schemas.google.com/contact/2008');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
            $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:batch', 'http://schemas.google.com/gdata/batch');

            // create category thing:
            $category = $batchFeed->createElement('category');
            $category->setAttribute('scheme', 'http://schemas.google.com/g/2005#kind');
            $category->setAttribute('term', 'http://schemas.google.com/g/2008#contact');
            $feed->appendChild($category);
            $batchFeed->appendChild($feed);

            $index = 1;
            /** @var DOMDocument $entry */
            foreach ($newBatch as $entry) {
                $node = $batchFeed->importNode($entry->documentElement, true);

                $batchId = $batchFeed->createElement('batch:id', $index);
                $batchOp = $batchFeed->createElement('batch:operation');
                $batchOp->setAttribute('type', 'insert');
                $node->appendChild($batchId);
                $node->appendChild($batchOp);
                $feed->appendChild($node);
                $index++;
            }

            // send to Google:
            $URL     = Variables::batchUri();
            $headers = Variables::getAuthHeadersForUpdate();

            $result = Requests::post($URL, $headers, $batchFeed->saveXML());
            Log::debug('Mass insert body: ' . $batchFeed->saveXML());
            Log::debug('Mass insert status code: ' . $result->status_code);
            Log::debug('Mass insert body: ' . $result->body);
        }

    }

    /**
     * @return bool|string
     */
    public function logout()
    {
        $URL     = 'https://www.google.com/accounts/AuthSubRevokeToken';
        $token   = session('access_token');
        $context = [
            'http' => [
                'method'  => 'GET',
                'header'  =>
                    'Authorization: AuthSub token="' . $token['access_token'] . '"' . "\r\n" .
                    'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
                    'User-Agent: GSharedContacts/0.1' . "\r\n",
                'content' => null,
            ],
        ];
        $context = stream_context_create($context);
        $result  = @file_get_contents($URL, null, $context);
        if ($result === false) {
            return 'Error while getting token.';
        }

        return true;
    }

    /**
     * @param DomDocument  $xml
     * @param              $code
     *
     * @return bool|string
     */
    public function update(DomDocument $xml, $code)
    {
        $token   = session('access_token');
        $URL     = 'https://www.google.com/m8/feeds/contacts/' . session('hd') . '/full/' . $code;
        $headers
                 = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\n";
        $opts    = [
            'http' => [
                'method'  => 'PUT',
                'header'  => $headers,
                'content' => $xml->saveXML(),
            ],
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'An error occurred while updating this contact.';
        }

        return true;
    }

    /**
     * @param                                                     $code
     * @param UploadedFile                                        $photo
     *
     * @return mixed|void
     */
    public function uploadPhoto($code, UploadedFile $photo)
    {
        $URL     = 'https://www.google.com/m8/feeds/photos/media/' . session('hd') . '/' . $code;
        $mime    = $photo->getClientMimeType();
        $token   = session('access_token');
        $headers = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: " . $mime . "\r\nIf-Match: *\r\nGData-Version: 3.0\r\n";
        //$filePath = $photo->getRealPath();
        $filePath = $_FILES['photo']['tmp_name'];
        $content  = file_get_contents($filePath);

        $opts    = [
            'http' => [
                'method'  => 'PUT',
                'header'  => $headers,
                'content' => $content,
            ],
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);

        //        $result = Requests::put(
        //            $URL, $headers, file_get_contents($filePath)
        //        );
    }

    /**
     * @param int $index
     *
     * @return string
     * @throws Exception
     */
    private function _getAllContacts($index = 1)
    {
        $URL     = Variables::UriWithStartIndex($index);
        $headers = Variables::getAuthorizationHeaders();
        $result  = Requests::get($URL, $headers);

        if ($result->status_code != 200) {
            Log::debug('_getAllContacts: ', ['code' => $result->status_code, 'body' => $result->body]);
            throw new Exception('Getting shared contacts generated error ' . $result->status_code . '. Please see the log files.');
        }

        return $result->body;
    }
}