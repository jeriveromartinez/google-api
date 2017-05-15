<?php
require_once __DIR__ . '/../vendor/google/apiclient/src/Google/autoload.php';

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
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfigFile(CLIENT_SECRET_PATH);
    $client->setIncludeGrantedScopes(true);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    if (file_exists(CREDENTIALS_PATH)) {
        $accessToken = file_get_contents(CREDENTIALS_PATH);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        header("Location: $authUrl");
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->refreshToken($client->getRefreshToken());
        file_put_contents(CREDENTIALS_PATH, $client->getAccessToken());
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
