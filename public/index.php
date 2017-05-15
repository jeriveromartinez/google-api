<?php
require_once __DIR__ . '/../vendor/autoload.php';

define('APPLICATION_NAME', 'Test');
define('CREDENTIALS_PATH', __DIR__ . '/../calendar/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/../client_secret.json');
define('SCOPES', implode(' ', array(
        Google_Service_Calendar::CALENDAR)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    session_start();

    if ((isset($_SESSION)) && (!empty($_SESSION))) {
        echo "There are cookies<br>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfigFile(CLIENT_SECRET_PATH);
    $client->setIncludeGrantedScopes(true);
    $client->setAccessType('offline');

    if (isset($_GET['code'])) {
        echo "<br>I got a code from Google = " . $_GET['code']; // You won't see this if redirected later
        $client->authenticate($_GET['code']);
        $_SESSION['token'] = $client->getAccessToken();
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        echo "<br>I got the token = " . $_SESSION['token']; // <-- not needed to get here unless location uncommented
    }

    if (isset($_SESSION['token'])) {
        echo "<br>Getting access";
        $client->setAccessToken($_SESSION['token']);
    }

    if ($client->getAccessToken()) {
        echo "<hr><font size=+1>I have access to your calendar</font>";
        $event = new Google_Service_Calendar($client);
        $cals = $service->calendarList->listCalendarList();
        print_r($cals);
        /*$event->setSummary('Halloween');
        $event->setLocation('The Neighbourhood');
        $start = new Google_EventDateTime();
        $start->setDateTime('2013-9-29T10:00:00.000-05:00');
        $event->setStart($start);
        $end = new Google_EventDateTime();
        $end->setDateTime('2013-9-29T10:25:00.000-05:00');
        $event->setEnd($end);
        $createdEvent = $cal->events->insert('###', $event);
        echo '<br><font size=+1>Event created</font>';

        echo '<hr><br><font size=+1>Already connected</font> (No need to login)';*/

    } else {
        $authUrl = $client->createAuthUrl();
        print "<hr><br><font size=+2><a href='$authUrl'>Connect Me!</a></font>";
    }
    return $client;
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
$calendarId = 'primary';
$optParams = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => TRUE,
    'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);

printf($results);

/*if (count($results->getItems()) == 0) {
    print "No upcoming events found.\n";
} else {
    print "Upcoming events:\n";
    foreach ($results->getItems() as $event) {
        $start = $event->start->dateTime;
        if (empty($start)) {
            $start = $event->start->date;
        }
        printf("%s (%s)\n", $event->getSummary(), $start);
    }
}*/
