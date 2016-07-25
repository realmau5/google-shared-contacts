<?php
namespace GContacts\Http\Controllers;

use GContacts\Google\SharedContactsInterface;


/**
 * Class PhotoController
 */
class PhotoController extends Controller
{
    /**
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @param $code
     */
    public function photo($code)
    {
        if ($code == 'null') {
            $entry = 'Photo not found';
        } else {
            $entry = $this->contacts->getPhoto($code);
        }

        if ($entry == 'Photo not found' || is_null($entry)) {
            // return dummy image.
            $image = public_path('assets/i/') . 'default_profile.jpg';
            header("Content-Type: image/jpeg");
            header("Content-Length: " . (string)(filesize($image)));
            echo file_get_contents($image);
            exit();
        } else {
            header("Content-Type: image/jpeg");
            echo $entry;
            exit();
        }

    }

} 