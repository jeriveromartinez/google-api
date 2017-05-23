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
        echo "<br>I got a code from Google = " . $_GET['code'];
        $client->authenticate($_GET['code']);
        $_SESSION['token'] = $client->getAccessToken();
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        echo "<br>I got the token = " . $_SESSION['token'];
    }

    if (isset($_SESSION['token'])) {
        echo "<br/>Getting access<br/>";
        $client->setAccessToken($_SESSION['token']);
    }

    if ($client->getAccessToken()) {
        $calendar = new Google_Service_Calendar($client);
        return $calendar;
    } else {
        $authUrl = $client->createAuthUrl();
        header("Location: $authUrl");
        print "<hr><br><font size=+2><a href='$authUrl'>Connect Me!</a></font>";
    }
}

/**
 * @param Google_Service_Calendar $calendar
 */
function getCalendarList($calendar)
{
    $calendarList = $calendar->calendarList->listCalendarList();
    $id = $calendarList->getItems()[0]->getSummary();

    $optParams = array(
        'timeMin' => date('c'),
    );
    $events = $calendar->events->listEvents('primary', $optParams);
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

    $data = [];
    /** @var Google_Service_Calendar_Event $event */
    foreach ($events->getItems() as $event) {
        $title = $event->getSummary();
        $desc = $event->getDescription();
        /** @var Google_Service_Calendar_EventDateTime $dateB */
        $dateB = $event->getStart();
        $dateE = $event->getEnd();
        $id = $event->id;

        if (empty($dateB))
            $dateB = $event->start->date;
        if (empty($dateE))
            $dateE = $event->end->date;

        printf("%s-> %s - %s (%s)-(%s)<br/>", $id, $title, $desc, $dateB->getDate(), $dateE->getDate());
    }
}


/**
 * @param Google_Service_Calendar $calendar
 */
function addEvent($calendar)
{
    $begin = new DateTime('2017-05-25');
    $end = new DateTime('2017-05-25');
    $event = new Google_Service_Calendar_Event();
    $event->setSummary('my test 2');
    $event->setLocation('The Neighbourhood');
    $start = new Google_Service_Calendar_EventDateTime();
    $start->setDateTime($begin->format(\DateTime::RFC3339));
    $event->setStart($start);
    $end = new Google_Service_Calendar_EventDateTime();
    $end->setDateTime($end->format(\DateTime::RFC3339));
    $event->setEnd($end);

    $saved = $calendar->events->insert('primary', $event);
    echo "Created $saved->id<br/>";
}

function getEspecific()
{
    if (isset($_GET['googleId'])) {
        $event = $this->getCalendar()->events->get('primary', $_GET['googleId']);
        echo $event->getSummary();
    }
}

// Get the API client and construct the service object.
$client = getClient();
//addEvent($client);
getCalendarList($client);
echo '<p>';
getEspecific();
echo '</p>';