<?php

namespace GSharedContacts\Http\Controllers;

use GSharedContacts\Feed\EntryParser;
use GSharedContacts\Google\SharedContactsInterface;
use Illuminate\Http\Request;
use League\Csv\Reader;
use View;

/**
 * Class MassCreateController
 *
 * @package GSharedContacts\Http\Controllers
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

    /**
     *
     */
    public function getExampleFile()
    {
        $file = file_get_contents(public_path('assets/exampleCSV.csv'));

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="example.csv"');
        echo $file;
        exit;
    }

    /**
     * @return View
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
        $file   = $request->file('csv');
        $reader = Reader::createFromPath($file->getRealPath());

        // fix delimiter.
        $reader->setDelimiter(';');
        $results = $reader->fetchOne(0);

        if (count($results) === 1) {
            $reader->setDelimiter(',');
        }
        $all    = $reader->fetchAll();
        foreach ($all as $index => $row) {
            if ($index > 0) {
                // parse data:
                // create array we can parse.
                $array = [
                    'namePrefix'     => $row[0],
                    'givenName'      => $row[1],
                    'additionalName' => $row[2],
                    'familyName'     => $row[3],
                    'nameSuffix'     => $row[4],
                    'birthday'       => null,
                ];
                for ($i = 5; $i <= 7; $i++) {
                    if (strlen($row[$i]) > 0) {
                        $array['phone'][] = [
                            'label'   => null,
                            'rel'     => 'Home',
                            'number'  => $row[$i],
                            'primary' => false,

                        ];
                    }
                }
                for ($i = 8; $i <= 10; $i++) {
                    if (strlen($row[$i]) > 0) {
                        $array['email'][] = [
                            'label'   => null,
                            'rel'     => 'Home',
                            'address' => $row[$i],
                            'primary' => false,
                        ];
                    }
                }

                $contact = EntryParser::parseFromArray($array);
                EntryParser::parseToXML($contact);
            }
        }

        return view('mass.uploaded');
    }

}