<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('SCOPES', implode(' ', array(
        Google_Service_Calendar::CALENDAR_READONLY)
));

session_start();
$client = new Google_Client();
$client->setApplicationName("Google Calendar PHP Starter Application");
$client->setClientId('481341427692-md2djtmjb3c8l053fqlcfa2bh0fphcke.apps.googleusercontent.com');
$client->setClientSecret('TJMJjsrHsnP8iMUrlk0L6LD4');
$client->setRedirectUri('https://googlecaltest.herokuapp.com/test.php');
$client->setScopes(SCOPES);
$client->setAccessType('offline');

if (!isset($_SESSION['token'])) {
    $authUrl = $client->createAuthUrl();
    echo '<a href="' + $authUrl + '" target="_blank">Authorize</a>';
}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
    $service = new Google_Service_Calendar();
    $optParams = array(
        'maxResults' => 10,
        'orderBy' => 'startTime',
        'singleEvents' => TRUE,
        'timeMin' => date('c'),
    );
    $listCalendar = $service->calendarList->listCalendarList()->getItems();
    $events = $service->events->listEvents('primary', $optParams);

    var_dump($listCalendar);
    var_dump($events);
}