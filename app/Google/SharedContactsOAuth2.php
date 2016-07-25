<?php
namespace GContacts\Google;

use DOMDocument;
use GContacts\Feed\Entry;
use GContacts\Feed\EntryParser;
use Log;
use Requests;
use Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zend\Feed\Reader\Reader;

/**
 * Class SharedContactsOAuth2
 *
 * @package GContacts\Google
 */
class SharedContactsOAuth2 implements SharedContactsInterface
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
        Log::debug('Start at index ' . $currentIndex);
        $loopCounter = 0;
        while ($moreContacts) {
            Log::debug('Loopcounter: ' . $loopCounter);

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

        $token  = Session::get('access_token');
        $result = Requests::get(
            $URL, [
                    'Authorization' => 'Bearer ' . $token->access_token,
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                    'GData-Version' => '3.0',
                ]
        );

        if ($result->status_code == 200) {
            $rawBody = $result->body;

            return $rawBody;
        } else {
            Log::error('Could not execute _getAllContacts(' . $index . ')');
            Log::error('Google responded: ' . print_r($result, true));

            return false;
        }
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
        $URL    = 'https://www.google.com/m8/feeds/photos/media/' . Session::get('hd') . '/' . $code;
        $token  = Session::get('access_token');
        $mime   = $photo->getClientMimeType();
        $result = Requests::put(
            $URL, [
            'Authorization' => 'Bearer ' . $token->access_token,
            'Content-Type'  => $mime,
            'GData-Version' => '3.0',
            'If-Match'      => '*'
        ], file_get_contents($photo->getRealPath())
        );
    }

    /**
     * @param DomDocument  $xml
     * @param              $code
     *
     * @return bool|string
     */
    public function update(DomDocument $xml, $code)
    {
        $token  = Session::get('access_token');
        $URL    = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full/' . $code;
        $result = Requests::put(
            $URL,
            [
                'Authorization' => 'Bearer ' . $token->access_token,
                'Content-Type'  => 'application/atom+xml',
                'GData-Version' => '3.0',
            ], $xml->saveXML()
        );
        if ($result->status_code != 200) {
            $message = 'The status code returned was "' . $result->status_code . '".';
            Log::error('Could not execute update([DomDocument], ' . $code . ') using URL ' . $URL);
            Log::error('Google responded: ' . print_r($result, true));
            if (!(strpos($result->raw, 'Token invalid') === false)) {
                $message .= ' The token was invalid. Please disconnect and retry.';
            }

            return $message;
        } else {
            return true;
        }
    }

    /**
     * @param Entry                 $contact
     * @param                       $code
     *
     * @return bool|string
     */
    public function delete(Entry $contact, $code)
    {
        $token  = Session::get('access_token');
        $URL    = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full/' . $code;
        $result = Requests::delete(
            $URL,
            [
                'Authorization' => 'Bearer ' . $token->access_token,
                'Content-Type'  => 'application/atom+xml',
                'GData-Version' => '3.0',
                'If-Match'      => $contact->getEtag()
            ]
        );
        if ($result->status_code != 200) {
            Log::error('Could not execute delete([GContacts\Feed\Entry], ' . $code . ') using URL ' . $URL);
            Log::error('Google responded: ' . print_r($result, true));
            $message = 'The status code returned was "' . $result->status_code . '".';
            if (!(strpos($result->raw, 'Token invalid') === false)) {
                $message .= ' The token was invalid. Please disconnect and retry.';
            }

            return $message;
        } else {
            return true;
        }
    }

    /**
     * @param DomDocument $xml
     *
     * @return bool|string
     */
    public function create(DomDocument $xml)
    {
        $token  = Session::get('access_token');
        $URL    = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full';
        $result = Requests::post(
            $URL,
            [
                'Authorization' => 'Bearer ' . $token->access_token,
                'Content-Type'  => 'application/atom+xml',
                'GData-Version' => '3.0',
            ], $xml->saveXML()
        );
        if ($result->status_code != 200 && $result->status_code != 201) {
            Log::error('Could not execute create([DomDocument]) using URL ' . $URL);
            Log::error('Google responded: ' . print_r($result, true));
            $message = 'The status code returned was "' . $result->status_code . '".';
            if (!(strpos($result->raw, 'Token invalid') === false)) {
                $message .= ' The token was invalid. Please disconnect and retry.';
            }

            return $message;
        } else {
            return true;
        }
    }

    /**
     * @param $code
     *
     * @return null|string
     */
    public function getPhoto($code)
    {
        $token = Session::get('access_token');
        $entry = $this->getContact($code);
        /** @var $link \GContacts\AtomType\Link */
        foreach ($entry->getLink() as $link) {
            if ($link->getRel() == 'http://schemas.google.com/contacts/2008/rel#photo') {
                // get photo from Google:
                $result = Requests::get(
                    $link->getHref(), [
                                        'Authorization' => 'Bearer ' . $token->access_token,
                                        'Content-Type'  => 'application/x-www-form-urlencoded',
                                        'GData-Version' => '3.0',
                                    ]
                );
                if ($result->status_code == 200) {
                    return $result->body;
                } else {
                    Log::error('Could not execute photo(' . $code . ')');
                    Log::error('Google responded: ' . print_r($result, true));
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @param $code
     *
     * @return Entry|string
     */
    public function getContact($code)
    {
        $URL    = 'https://www.google.com/m8/feeds/contacts/' . Session::get('hd') . '/full/' . $code;
        $token  = Session::get('access_token');
        $result = Requests::get(
            $URL, [
                    'Authorization' => 'Bearer ' . $token->access_token,
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                    'GData-Version' => '3.0',
                ]
        );
        if ($result->status_code == 200) {
            $rawBody = $result->body;

            return EntryParser::parseFromXML($rawBody);
        } else {
            Log::error('Could not execute _getContact(' . $code . ') using URL ' . $URL);
            Log::error('Google responded: ' . print_r($result, true));

            return 'Get contact with ID "' . $code . '" returned an error: ' . $result->status_code;
        }

    }

    /**
     * @param $tempToken
     *
     * @return mixed|void
     */
    public function getToken($tempToken)
    {
        die('Not valid in this context.');
    }

    /**
     * @return bool
     */
    public function logout()
    {
        $token  = Session::get('access_token');
        $URL    = 'https://accounts.google.com/o/oauth2/revoke?token=' . $token->access_token;
        $result = Requests::get($URL);

        return true;
    }
}