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

    if (isset($_SESSION) && (!empty($_SESSION))) {
        echo 'There are cookies<br>';
        echo '<pre>';
        print_r($_SESSION);
        echo '</pre>';
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

    return $client;
}

function getCalendarList($client)
{
    if ($client->getAccessToken()) {
        echo '<hr><font size=+1>I have access to your calendar</font>';
        $calendar = new Google_Service_Calendar($client);
        $calendarList = $calendar->calendarList->listCalendarList();

        while (true) {
            foreach ($calendarList->getItems() as $calendarListEntry) {
                echo $calendarListEntry->getSummary();
            }
            $pageToken = $calendarList->getNextPageToken();
            if ($pageToken) {
                $optParams = array('pageToken' => $pageToken);
                $calendarList = $calendar->calendarList->listCalendarList($optParams);
            } else {
                break;
            }
        }
    } else {
        $authUrl = $client->createAuthUrl();
        print "<hr><br><font size=+2><a href='$authUrl'>Connect Me!</a></font>";
    }
}

// Get the API client and construct the service object.
$client = getClient();
getCalendarList($client);

// Print the next 10 events on the user's calendar.
/*$calendarId = 'primary';
$optParams = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => TRUE,
    'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);

printf($results);*/

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
