<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ddfmaster;
use Carbon\Carbon;
use App\phRETS;

//require ('/vendor/autoload.php');

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {



        return view('home');
    }

    public function ddf1()
    {
        $ddf = new ddfmaster;
        $ddf->lastUpdated = Carbon::now();
        $ddf->ddf_id = 234234;
        $ddf->save();
        dd($ddf);
    }


    public function ddf()
    {
        $RETSURL = "http://data.crea.ca/Login.svc/Login";
        $RETSUsername = "uXvnxiisaUUerrMBzJHJieqh";
        $RETSPassword = "vpCsrh5Qbw3yxrVUA0WJGRw0";

        $log = new \Monolog\Logger('PHRETS');
        $log->pushHandler( new \Monolog\Handler\StreamHandler(getcwd().'/tmp/mono.log', \Monolog\Logger::DEBUG) );

        date_default_timezone_set('America/Los_Angeles');


        $config = new \PHRETS\Configuration;
        $config->setLoginUrl($RETSURL)
                ->setUsername($RETSUsername)
                ->setPassword($RETSPassword)
                ->setRetsVersion('1.7.2');

        $rets = new \PHRETS\Session($config);

        // If you're using Monolog already for logging, you can pass that logging instance to PHRETS for some additional
        // insight into what PHRETS is doing.
        //
        // $log = new \Monolog\Logger('PHRETS');
        // $log->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));
        // $rets->setLogger($log);

        $connect = $rets->Login();

        $system = $rets->GetSystemMetadata();

        $resources = $system->getResources();
        $classes = $resources->first()->getClasses();

        $classes = $rets->GetClassesMetadata('Property');
      //  print_r($classes->first());

        //$objects = $rets->GetObject('Property', 'Photo', '00-1669', '*', 1);
       // print_r($objects);

        //$fields = $rets->GetTableMetadata('Property', 'A');
       // print_r($fields[0]);

        $results = $rets->Search('Property', 'A', '*', ['Limit' => 3, 'Select' => 'LIST_1,LIST_105,LIST_15,LIST_22,LIST_87,LIST_133,LIST_134']);
        foreach ($results as $r) {
            print_r($r);
        }


exit();






        $config = new \PHRETS\Configuration;
        $config->setLoginUrl($RETSURL);
        $config->setUsername($RETSUsername);
        $config->setPassword($RETSPassword);
        $config->setUserAgent('DansAgent/1.0');
        $config->setUserAgentPassword('');
        $config->setRetsVersion('1.7.2');

        $session = new \PHRETS\Session($config);
        $session->setLogger($log);

        $login = $session->Login();

        $system = $session->GetSystemMetadata();

        $timestamp_field = 'LastUpdated';
        $property_classes = ['Property', 'Agent', 'Office'];

foreach ($property_classes as $pc) {
    // generate the DMQL query
    $query = "({$timestamp_field}=2000-01-01T00:00:00+)";

    // make the request and get the results
    $results = $session->Search('Property', $pc, $query);

    // save the results in a local file
    dd($results);
    //file_put_contents('data/Property_' . $pc . '.csv', $results->toCSV());
}


        //$results = $session->Search('Property', 'A', '*');
        dd($system);

    }


    public function ddf2()
    {

        $log = new \Monolog\Logger('PHRETS');
        $log->pushHandler( new \Monolog\Handler\StreamHandler(getcwd().'/tmp/mono.log', \Monolog\Logger::DEBUG) );

        // Lots of output, saves requests to a local file.
        $debugMode = false;
        $TimeBackPull = "-2 years";
        $TimeBackPull = "-4 hours";

        /* RETS Variables */

        $RETS = new PHRets();
        $RETSURL = "http://data.crea.ca/Login.svc/Login";
        $RETSUsername = "uXvnxiisaUUerrMBzJHJieqh";
        $RETSPassword = "vpCsrh5Qbw3yxrVUA0WJGRw0";
        $RETS->Connect($RETSURL, $RETSUsername, $RETSPassword);
        $RETS->AddHeader("RETS-Version", "RETS/1.7.2");
        $RETS->AddHeader('Accept', '/');
        $RETS->SetParam('compression_enabled', true);
        $RETS_PhotoSize = "LargePhoto";
        $RETS_LimitPerQuery = 100;

        if ($debugMode /* DEBUG OUTPUT */) {
            //$RETS->SetParam("catch_last_response", true);
            $debugFile = storage_path() . "/debug.txt";
            touch($debugFile);
            $RETS->SetParam("debug_file", $debugFile);
            $RETS->SetParam("debug_mode", true);
            echo "<hr>" . $debugFile . "<hr>";
        }



        /* NOTES
 * With CREA, You have to ask the RETS server for a list of IDs.
 * Once you have these IDs, you can query for 100 listings at a time
 * Example Procedure:
 * 1. Get IDs (500 Returned)
 * 2. Get Listing Data (1-100)
 * 3. Get Listing Data (101-200)
 * 4. (etc)
 * 5. (etc)
 * 6. Get Listing Data (401-500)
 *
 * Each time you get Listing Data, you want to save this data and then download it's images...
 */

        error_log("-----GETTING ALL ID's-----");
        $DBML = "(LastUpdated=" . date('Y-m-d', strtotime($TimeBackPull)) . ")";
        $params = array("Limit" => 1, "Format" => "STANDARD-XML", "Count" => 1);

        $results = $RETS->SearchQuery("Property", "Property", $DBML, $params);

        $totalAvailable = $results["Count"];
        error_log("-----" . $totalAvailable . " Found-----");
       // print("-----" . $totalAvailable . " Found-----</br>" . $DBML . "<br>");
        if (empty($totalAvailable) || $totalAvailable == 0)
            error_log(print_r($RETS->GetLastServerResponse(), true));
        for ($i = 0; $i < ceil($totalAvailable / $RETS_LimitPerQuery); $i++) {
            $startOffset = $i * $RETS_LimitPerQuery;

            error_log("-----Get IDs For " . $startOffset . " to " . ($startOffset + $RETS_LimitPerQuery) . ". Mem: " . round(memory_get_usage() / (1024 * 1024), 1) . "MB-----");
            $params = array("Limit" => $RETS_LimitPerQuery, "Format" => "STANDARD-XML", "Count" => 1, "Offset" => $startOffset);
            $results = $RETS->SearchQuery("Property", "Property", $DBML, $params);
            $tick = 0;
            //print "<table width=400>";
            foreach ($results["Properties"] as $listing) {
                $listingID = $listing["@attributes"]["ID"];
                if ($debugMode) error_log($listingID);
                //print "<tr><td>" . $tick++ . "</td><td>" . $listingID . "</td><td>" . $listing["@attributes"]["LastUpdated"] . "</td></tr>";


                /* @TODO Handle $listing array. Save to Database? */

                /* @TODO Uncomment this line to begin saving images. Refer to function at top of file */
                //downloadPhotos($listingID);
            }
          //  print "</table>";
        }

        $RETS->Disconnect();

        return view('ddf');
    }

    function downloadPhotos($listingID)
    {
        global $RETS, $RETS_PhotoSize, $debugMode;

        if (!$downloadPhotos) {
            if ($debugMode) error_log("Not Downloading Photos");
            return;
        }

        $photos = $RETS->GetObject("Property", $RETS_PhotoSize, $listingID, '*');

        if (!is_array($photos)) {
            if ($debugMode) error_log("Cannot Locate Photos");
            return;
        }

        if (count($photos) > 0) {
            $count = 0;
            foreach ($photos as $photo) {
                if (
                    (!isset($photo['Content-ID']) || !isset($photo['Object-ID']))
                    ||
                    (is_null($photo['Content-ID']) || is_null($photo['Object-ID']))
                    ||
                    ($photo['Content-ID'] == 'null' || $photo['Object-ID'] == 'null')
                ) {
                    continue;
                }

                $listing = $photo['Content-ID'];
                $number = $photo['Object-ID'];
                $destination = $listingID . "_" . $number . ".jpg";
                $photoData = $photo['Data'];

                /* @TODO SAVE THIS PHOTO TO YOUR PHOTOS FOLDER
         * Easiest option:
         * 	file_put_contents($destination, $photoData);
         * 	http://php.net/function.file-put-contents
         */

                $count++;
            }

            if ($debugMode)
                error_log("Downloaded " . $count . " Images For '" . $listingID . "'");
        } elseif ($debugMode)
            error_log("No Images For '" . $listingID . "'");

        // For good measure.
        if (isset($photos)) $photos = null;
        if (isset($photo)) $photo = null;
    }
}
