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
 * @return Google_Service_Calendar the authorized client object
 */
function getClient()
{
    session_start();

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
        $calendar = new Google_Service_Calendar($client);
        return $calendar;
    } else {
        $authUrl = $client->createAuthUrl();
        header("Location: $authUrl");
        // print "<hr><br><font size=+2><a href='$authUrl'>Connect Me!</a></font>";
    }


}

/**
 * @param Google_Service_Calendar $calendar
 */
function getCalendarList($calendar)
{
    echo '<hr><font size=+1>I have access to your calendar</font><br/>';

    $calendarList = $calendar->calendarList->listCalendarList();
    $id = $calendarList->getItems()[0]->getSummary();

    $optParams = array(
        'maxResults' => 10,
        'orderBy' => 'startTime',
    );
    $events = $calendar->events->listEvents('primary');
    echo "$id<br/>";

    /*while (true) {
        foreach ($events->getItems() as $event) {
            $title = $event->getSummary();
            $desc = $event->getDescription();
            echo "<p>$title<div>$desc</div></p>";
        }
        $pageToken = $events->getNextPageToken();
        if ($pageToken) {
            $optParams = array('pageToken' => $pageToken);
            $events = $calendar->events->listEvents('primary', $optParams);
        } else {
            break;
        }
    }*/

    /** @var Google_Service_Calendar_Event $event */
    foreach ($events->getItems() as $event) {
        //$start = $event->start->dateTime;
        //print_r($event);
        $title = $event->getSummary();
        $desc = $event->getDescription();
        /** @var Google_Service_Calendar_EventDateTime $dateB */
        $dateB = $event->getStart();
        $dateE = $event->getEnd();

        if (empty($dateB))
            $dateB = $event->start->date;
        if (empty($dateE))
            $dateE = $event->end->date;

        printf("%s - %s (%s)-(%s)<br/>", $title, $desc, $dateB->getDate(), $dateE->getDate());
    }
}


function addEvent($client)
{
    if ($client->getAccessToken()) {

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
