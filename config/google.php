<?php

return [
    'client_id'    => env('GOOGLE_ID'),
    'secret'       => env('GOOGLE_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT'),
    'scopes'       => 'https://www.google.com/m8/feeds/',
    'app-scope'    => 'personal',
    'ga'           => 'xx-xx-xxxx-xx',
];