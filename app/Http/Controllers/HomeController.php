<?php

namespace GSharedContacts\Http\Controllers;

use GSharedContacts\Google\SharedContactsInterface;
use Log;

/**
 * Class HomeController
 */
class HomeController extends Controller
{

    /** @var SharedContactsInterface  */
    public $contacts;

    /**
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('index');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('privacy');
    }

    /**
     * @return $this
     */
    public function home()
    {
        $list = $this->contacts->all();
        if (!is_array($list)) {
            return view('error')->with('message', $list);
        }
        return view('home')->with('contacts', $list);
    }
}
