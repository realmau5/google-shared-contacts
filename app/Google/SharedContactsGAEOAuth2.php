<?php
namespace GSharedContacts\Google;

use DOMDocument;
use GSharedContacts\Feed\Entry;
use GSharedContacts\Feed\EntryParser;
use Log;
use Requests;
use Session;
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
        // this is where we start getting contacts:
        // we begin with nothing:
        $return = [];

        // we get a bunch of contacts in a loop:
        $moreContacts = true;
        $currentIndex = 1;
        \Log::debug('Start at index ' . $currentIndex);
        $loopCounter = 0;
        while ($moreContacts) {
            \Log::debug('Loopcounter: ' . $loopCounter);

            // get the nth set:
            $rawBody = $this->_getAllContacts($currentIndex);
            // if error, break out.
            if ($rawBody === false) {
                $moreContacts = false;
                break;
            }
            // and we parse the current set:
            $feed = Reader::importString($rawBody);

            // and add them to the return list.
            if ($feed->count() > 0) {
                foreach ($feed as $entry) {
                    $current  = EntryParser::parseFromXML($entry->saveXml());
                    $return[] = $current;
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
     * @param int $index
     *
     * @return bool|string
     */
    private function _getAllContacts($index = 1)
    {
        $URL = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full';
        $URL .= '?max-results=25&start-index=' . $index;

        $token = Session::get('access_token');

        $headers
              = "Authorization: AuthSub token=\"{$token['access_token']}\"\r\nContent-Type: application/x-www-form-urlencoded\r\nGData-Version: 3.0\r\n";
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => $headers
            ]
        ];

        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return false;
        }


        $isError = strpos($result, 'Error 403 (Forbidden)');
        if ($isError) {
            return false;
        }
        return $result;


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
     * @param                                                     $code
     * @param UploadedFile                                        $photo
     *
     * @return mixed|void
     */
    public function uploadPhoto($code, UploadedFile $photo)
    {
        $URL     = 'https://www.google.com/m8/feeds/photos/media/' . Session::get('hd') . '/' . $code;
        $mime    = $photo->getClientMimeType();
        $token   = Session::get('access_token');
        $headers = "Authorization: AuthSub token=\"{$token->access_token}\"\r\nContent-Type: " . $mime . "\r\nIf-Match: *\r\nGData-Version: 3.0\r\n";
        //$filePath = $photo->getRealPath();
        $filePath = $_FILES['photo']['tmp_name'];
        $content  = file_get_contents($filePath);

        $opts    = [
            'http' => [
                'method'  => 'PUT',
                'header'  => $headers,
                'content' => $content
            ]
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);

//        $result = Requests::put(
//            $URL, $headers, file_get_contents($filePath)
//        );
    }

    /**
     * @param DomDocument  $xml
     * @param              $code
     *
     * @return bool|string
     */
    public function update(DomDocument $xml, $code)
    {
        $token   = Session::get('access_token');
        $URL     = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full/' . $code;
        $headers
                 = "Authorization: AuthSub token=\"{$token->access_token}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\n";
        $opts    = [
            'http' => [
                'method'  => 'PUT',
                'header'  => $headers,
                'content' => $xml->saveXML()
            ]
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'An error occurred while updating this contact.';
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
        $token   = Session::get('access_token');
        $URL     = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full/' . $code;
        $headers = "Authorization: AuthSub token=\"{$token->access_token}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\nIf-Match: "
            . $contact->getEtag() . "\r\n";

        $opts    = [
            'http' => [
                'method' => 'DELETE',
                'header' => $headers
            ]
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'Error while retreiving contact.';
        }
        return true;
    }

    /**
     * @param DomDocument $xml
     *
     * @return bool|string
     */
    public function create(DomDocument $xml)
    {
        $URL   = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full';
        $token = Session::get('access_token');

        $headers
              = "Authorization: AuthSub token=\"{$token->access_token}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\n";
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => $headers,
                'content' => $xml->saveXML()
            ]
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
                $token   = Session::get('access_token');
                $headers
                         = "Authorization: AuthSub token=\"{$token->access_token}\"\r\nContent-Type: application/atom+xml\r\nGData-Version: 3.0\r\n";
                $opts    = [
                    'http' => [
                        'method' => 'GET',
                        'header' => $headers,
                    ]
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
     * @param $code
     *
     * @return \GSharedContacts\Feed\Entry|string
     */
    public function getContact($code)
    {
        $URL     = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full/' . $code;
        $token   = Session::get('access_token');
        $headers
                 = "Authorization: AuthSub token=\"{$token->access_token}\"\r\nContent-Type: application/x-www-form-urlencoded\r\nGData-Version: 3.0\r\n";
        $opts    = [
            'http' => [
                'method' => 'GET',
                'header' => $headers
            ]
        ];
        $context = stream_context_create($opts);
        $result  = @file_get_contents($URL, false, $context);
        if ($result === false) {
            return 'Error while retreiving contact.';
        }

        return EntryParser::parseFromXML($result);
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
                'content' => null
            ]
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
     * @return bool|string
     */
    public function logout()
    {
        $URL     = 'https://www.google.com/accounts/AuthSubRevokeToken';
        $token   = Session::get('access_token');
        $context = [
            'http' => [
                'method'  => 'GET',
                'header'  =>
                    'Authorization: AuthSub token="' . $token->access_token . '"' . "\r\n" .
                    'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
                    'User-Agent: GSharedContacts/0.1' . "\r\n",
                'content' => null
            ]
        ];
        $context = stream_context_create($context);
        $result  = @file_get_contents($URL, null, $context);
        if ($result === false) {
            return 'Error while getting token.';
        }
        return true;
    }
}