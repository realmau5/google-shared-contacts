<?php

namespace GContacts\Http\Controllers;
use GContacts\Google\SharedContactsInterface;
use GContacts\AtomType\Email;
use GContacts\AtomType\Im;
use GContacts\AtomType\Organization;
use GContacts\AtomType\Phonenumber;
use GContacts\AtomType\StructuredPostalAddress;
/**
 * Class RpcController
 */
class RpcController extends Controller
{
    /**
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @param $key
     * @param $index
     *
     * @return string
     */
    public function addRow($key, $index)
    {
        // map some stuff:
        $keys      = [
            'organization'            =>
                [
                    'tpl' => 'organization',
                    'arr' => 'organization',
                ],
            'phoneNumber'             =>
                [
                    'tpl' => 'phone',
                    'arr' => 'phoneNumber'
                ]
            ,
            'structuredPostalAddress' =>
                [
                    'tpl' => 'address',
                    'arr' => 'structuredPostalAddress'
                ]
        ];
        $tpl       = isset($keys[$key]) ? $keys[$key]['tpl'] : $key;
        $indexName = isset($keys[$key]) ? $keys[$key]['arr'] : $key;

        $index = $index - 1;


        $contact = [
            $indexName => [
                $index => []
            ]
        ];

        $defaultOrgRels     = Organization::getDefaultRels();
        $defaultPhoneRels   = Phonenumber::getDefaultRels();
        $defaultImRels      = Im::getDefaultRels();
        $defaultImProtocols = Im::getDefaultProtocols();
        $defaultAddrRels    = StructuredPostalAddress::getDefaultRels();
        $defaultAddrMail    = StructuredPostalAddress::getDefaultMailClasses();
        $defaultAddrUsage   = StructuredPostalAddress::getDefaultUsages();
        $defaultMailRels    = Email::getDefaultRels();


        $view = view('edit/' . $tpl, compact('contact', 'defaultOrgRels', 'defaultPhoneRels',
                                             'defaultAddrMail','defaultMailRels',
                                             'defaultAddrUsage',
                                             'defaultImRels', 'defaultImRels', 'defaultImProtocols', 'defaultAddrRels'));
        $html = $view->render();
        return $html;
    }

} 