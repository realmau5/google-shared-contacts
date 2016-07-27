<?php

namespace GContacts\Http\Controllers;

use GContacts\Feed\EntryParser;
use GContacts\Google\SharedContactsInterface;
use Illuminate\Http\Request;
use Input;
use View;

/**
 * Class MassCreateController
 *
 * @package GContacts\Http\Controllers
 */
class MassCreateController extends Controller
{

    /**
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
    }

    public function getExampleFile() {
        $file = file_get_contents(public_path('assets/exampleCSV.csv'));

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="example.csv"');
        echo $file;
        exit;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('mass.index');
    }

    /**
     *
     */
    public function upload(Request $request)
    {
        // get the file:
        if (!$request->hasFile('csv')) {
            return view('error')->with('message', 'Pls upload something.');
        }
        $file     = $request->file('csv');
        //$fileName = $file->getRealPath();
        $fileName = $_FILES['csv']['tmp_name'];
        //var_dump($_FILES);exit;

        $errors   = [];

        // parse using fgetcsv
        $row = 1;
        if (($handle = fopen($fileName, 'r')) !== false) {
            while (($data = fgetcsv($handle, 2048, ',')) !== false) {
                if ($row > 1) {
                    // get values:
                    $email = [$data[8], $data[9], $data[10]];

                    // create array we can parse.
                    $array = [
                        'namePrefix'     => $data[0],
                        'givenName'      => $data[1],
                        'additionalName' => $data[2],
                        'familyName'     => $data[3],
                        'nameSuffix'     => $data[4],
                        'birthday'       => null,
                    ];
                    for ($i = 5; $i <= 7; $i++) {
                        if (strlen($data[$i]) > 0) {
                            $array['phone'][] = [
                                'label'   => null,
                                'rel'     => 'Home',
                                'number'  => $data[$i],
                                'primary' => false,

                            ];
                        }
                    }
                    for ($i = 8; $i <= 10; $i++) {
                        if (strlen($data[$i]) > 0) {
                            $array['email'][] = [
                                'label'   => null,
                                'rel'     => 'Home',
                                'address' => $data[$i],
                                'primary' => false,
                            ];
                        }
                    }

                    $contact    = EntryParser::parseFromArray($array);
                    $contactXML = EntryParser::parseToXML($contact);

                    $result = $this->contacts->create($contactXML);
                    if (!($result === true)) {
                        $errors[] = $result;
                    }
                }

                $row++;

            }
            fclose($handle);
        }

        return view('mass.uploaded', compact('errors'));
    }

}