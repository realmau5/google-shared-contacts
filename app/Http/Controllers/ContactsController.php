<?php
namespace GSharedContacts\Http\Controllers;

use GSharedContacts\AtomType\Email;
use GSharedContacts\AtomType\Im;
use GSharedContacts\AtomType\Organization;
use GSharedContacts\AtomType\Phonenumber;
use GSharedContacts\AtomType\StructuredPostalAddress;
use GSharedContacts\Feed\EntryParser;
use GSharedContacts\Google\SharedContactsInterface;
use Illuminate\Http\Request;
use Redirect;
use View;

/**
 * Class ContactsController
 */
class ContactsController extends Controller
{
    /** @var SharedContactsInterface */
    public $contacts;

    /**
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @return View
     */
    public function add()
    {
        return view('add');
    }

    /**
     * @param $code
     *
     * @return View
     */
    public function delete($code)
    {
        $contact = $this->contacts->getContact($code);

        return view('delete', compact('contact'));
    }

    /**
     * @param $code
     *
     * @return View
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
     * @return string
     */
    public function editPhoto()
    {
        return 'todo, sorry.';
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function massDelete(Request $request)
    {
        $delete   = $request->get('delete');
        $contacts = [];
        if (is_array($delete)) {

            foreach ($delete as $code) {
                $contacts[] = $this->contacts->getContact($code);
            }

            return view('massdelete', compact('contacts'));
        }

        return view('error')->with('message', 'Please select somebody.');
    }

    /**
     * @param Request $request
     *
     * @return Redirect
     */
    public function postAdd(Request $request)
    {
        $contact = EntryParser::parseFromArray($request->all());

        $contactXML = EntryParser::parseToXML($contact);

        $result = $this->contacts->create($contactXML);
        if ($result === true) {
            return redirect(route('home'));
        } else {
            return view('error')->with('message', $result);
        }
    }

    /**
     * @param $code
     *
     * @return Redirect
     */
    public function postDelete($code)
    {

        $contact = $this->contacts->getContact($code);
        $result  = $this->contacts->delete($contact, $code);
        if ($result === true) {
            return redirect(route('home'));
        } else {
            // $error contains the error message!
            return view('error')->with('message', $result);
        }

    }

    /**
     * @param Request $request
     * @param         $code
     *
     * @return Redirect
     */
    public function postEdit(Request $request, $code)
    {

        if ($request->hasFile('photo')) {
            $this->contacts->uploadPhoto($code, $request->file('photo'));
        }
        $contact    = EntryParser::parseFromArray($request->all());
        $contactXML = EntryParser::parseToXML($contact);
        $result     = $this->contacts->update($contactXML, $code);

        if ($result === true) {
            return redirect(route('contacts.edit', [$code]));
        } else {
            return view('error')->with('message', $result);
        }
    }

    /**
     * @param Request $request
     *
     * @return Redirect
     */
    public function reallyMassDelete(Request $request)
    {
        $delete = $request->get('code');
        if (is_array($delete)) {
            foreach ($delete as $code) {
                // delete them!
                $contact = $this->contacts->getContact($code);
                $this->contacts->delete($contact, $code);
            }

            return redirect(route('home'));
        }

        return view('error')->with('message', 'Could not delete.');
    }


}