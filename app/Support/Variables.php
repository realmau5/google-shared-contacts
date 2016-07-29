<?php
declare(strict_types = 1);

namespace GSharedContacts\Support;

use Log;

/**
 * Class Variables
 *
 * @package GSharedContacts\Support
 */
class Variables
{
    /**
     * @param int $index
     *
     * @return string
     */
    public static function UriWithStartIndex(int $index):string
    {
        $URL    = 'https://www.google.com/m8/feeds/contacts/%s/full?max-results=25&start-index=%d';
        $parsed = sprintf($URL, session('hd'), $index);
        Log::debug('Generated URL: ' . $parsed);

        return $parsed;

    }

    public static function getAuthorizationHeaders(): array
    {

        $token   = session('access_token');
        $headers = [
            'Authorization' => sprintf('AuthSub token="%s"', $token['access_token']),
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'GData-Version' => '3.0',
        ];

        Log::debug('Generated headers', $headers);

        return $headers;
    }

}