<?php
namespace GContacts\Http\Controllers;

use GContacts\AtomType\Email;
use GContacts\AtomType\Im;
use GContacts\AtomType\Organization;
use GContacts\AtomType\Phonenumber;
use GContacts\AtomType\StructuredPostalAddress;
use GContacts\Feed\EntryParser;
use GContacts\Google\SharedContactsInterface;
use Input;
use Redirect;
use View;

/**
 * Class ContactsController
 */
class ContactsController extends Controller
{

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
    public function add()
    {
        return view('add');
    }

    /**
     * @param $code
     *
     * @return $this
     */
    public function delete($code)
    {
        $contact = $this->contacts->getContact($code);
        return view('delete', compact('contact'));
    }

    /**
     * Mass delete confirmation.
     *
     * @param $code
     *
     * @return $this
     */
    public function massDelete()
    {
        $delete   = Input::get('delete');
        $contacts = [];
        if (is_array($delete)) {

            foreach ($delete as $code) {
                $contacts[] = $this->contacts->getContact($code);
            }
            return view('massdelete', compact('contacts'));
        }

        return View::make('error')->with('message', 'Please select somebody.');
    }

    /**
     * Actually mass delete people.
     *
     * @return View
     */
    public function reallyMassDelete()
    {
        $delete = Input::get('code');
        if (is_array($delete)) {
            foreach ($delete as $code) {
                // delete them!
                $contact = $this->contacts->getContact($code);
                $this->contacts->delete($contact, $code);
            }
            return Redirect::route('home');
        }
        return View::make('error')->with('message', 'Could not delete.');
    }

    /**
     * @param $code
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postDelete($code)
    {

        $contact = $this->contacts->getContact($code);
        $result  = $this->contacts->delete($contact, $code);
        if ($result === true) {
            return Redirect::route('home');
        } else {
            // $error contains the error message!
            return view('error')->with('message', $result);
        }

    }

    /**
     * @param $code
     *
     * @return $this
     */
    public function edit($code)
    {
        $fullContact = $this->contacts->getContact($code);

        if (is_string($fullContact)) {
            return view('error')->with('message', $fullContact);
        } else {

            $defaultOrgRels     = Organization::getDefaultRels();
            $defaultPhoneRels   = Phonenumber::getDefaultRels();
            $defaultImRels      = Im::getDefaultRels();
            $defaultImProtocols = Im::getDefaultProtocols();
            $defaultAddrRels    = StructuredPostalAddress::getDefaultRels();
            $defaultAddrMail    = StructuredPostalAddress::getDefaultMailClasses();
            $defaultAddrUsage   = StructuredPostalAddress::getDefaultUsages();
            $defaultMailRels    = Email::getDefaultRels();

            $contact = EntryParser::parseToArray($fullContact);
            return view(
                'edit', compact(
                          'code', 'contact', 'fullContact', 'defaultOrgRels', 'defaultPhoneRels',
                          'defaultAddrMail', 'defaultMailRels',
                          'defaultAddrUsage',
                          'defaultImRels', 'defaultImRels', 'defaultImProtocols', 'defaultAddrRels'
                      )
            );
        }
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postAdd()
    {
        $contact = EntryParser::parseFromArray(Input::all());

        $contactXML = EntryParser::parseToXML($contact);

        $result = $this->contacts->create($contactXML);
        if ($result === true) {
            return Redirect::route('home');
        } else {
            return View::make('error')->with('message', $result);
        }
    }

    /**
     * @param $code
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postEdit($code)
    {

        if (Input::hasFile('photo')) {
            $this->contacts->uploadPhoto($code, Input::file('photo'));
        }
        $contact    = EntryParser::parseFromArray(Input::all());
        $contactXML = EntryParser::parseToXML($contact);
        $result     = $this->contacts->update($contactXML, $code);

        if ($result === true) {
            return Redirect::route('contacts.edit', [$code]);
        } else {
            return View::make('error')->with('message', $result);
        }
    }

    /**
     * @return string
     */
    public function editPhoto()
    {
        return 'todo, sorry.';
    }


}