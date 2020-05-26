<?php

namespace AliasProject\Google;

use Google_Client, Google_Service_Calendar, Google_Service_Calendar_Event;

class Calendar
{
    private $client = null;
    private $service = null;

    /**
     * Create new instance
     *
     * @param string $config - API Config
     * @param string $applicationName - Application Name
     */
    public function __construct($config = false, string $applicationName = 'Google Calendar')
    {
        // Set Client
        $this->client = new Google_Client();

        // Set Authentication
        if (empty($config)) {
            $this->client->useApplicationDefaultCredentials();
        } else {
            $this->client->setAuthConfig($config);
        }

        $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        $this->client->setApplicationName($applicationName);
        $this->client->setAccessType('offline');

        // Set Service
        $this->service = new Google_Service_Calendar($this->client);
    }

    /**
     * Add or update subscriber
     *
     * @param string $email - Subscriber Email
     * @param string $drip_id - Current user Drip ID for update
     * @param array $custom_fields - Array of custom fields to save to user
     */
    public function add(string $calendar_id, string $summary, string $location = '', string $description = '', string $start, string $end, $timezone, array $recurrence = [], array $attendees, array $reminders = [])
    {
        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'location' => $location,
            'description' => $description,
            'start' => [
                'dateTime' => $start,
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end,
                'timeZone' => $timezone,
            ],
            'recurrence' => $recurrence,
            'attendees' => $attendees,
            'reminders' => $reminders,
        ]);

        $event = $this->service->events->insert($calendar_id, $event);

        return $event;
    }

    public function read(string $calendar_id)
    {
        // $optParams = [
        //     'maxResults' => 10,
        //     'orderBy' => 'startTime',
        //     'singleEvents' => true,
        //     'timeMin' => date('c'),
        // ];
        $response = $this->service->events->listEvents($calendar_id, $optParams);
        $events = $response->getItems();

        return $events;
    }
}
