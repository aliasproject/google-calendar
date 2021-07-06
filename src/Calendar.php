<?php

namespace AliasProject\Google;

use Google_Client, Google_Service_Calendar, Google_Service_Calendar_Event, Google_Service_Calendar_FreeBusyRequest, Google_Service_Calendar_FreeBusyRequestItem;

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

        $this->client->addScope(Google_Service_Calendar::CALENDAR);
        $this->client->setApplicationName($applicationName);
        $this->client->setAccessType('offline');

        // Set Service
        $this->service = new Google_Service_Calendar($this->client);
    }

    /**
     * Create a new event
     *
     * @param string $calendar_id - Calendar ID
     * @param string $summary - Summary of event
     * @param string $location - Location of event
     * @param string $description - Description of event
     * @param string $start - Start date / time of event
     * @param string $end - End date / time of event
     * @param string $timezone - Timezone of event
     * @param array $recurrence - Recurrence of event
     * @param array $attendees - Attendees for event
     */
    public function createEvent(string $calendar_id, string $summary, string $start, string $end, string $timezone, string $location = '', string $description = '', array $recurrence = [], array $attendees = [], array $reminders = [])
    {
        // Format start / end times
        $start = date("c", strtotime($start));
        $end = date("c", strtotime($end));

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
            'recurrence' => $recurrence, // [ 'RRULE:FREQ=DAILY;COUNT=2' ]
            'attendees' => $attendees,
            'reminders' => $reminders,
        ]);

        $event = $this->service->events->insert($calendar_id, $event);

        return $event;
    }

    /**
     * List events
     *
     * @param string $calendar_id - Calendar ID
     * @param string $date - Only return events for specific date
     * @param string $timezone - Results based on specific timezone
     */
    public function listEvents(string $calendar_id, string $date = '', string $timezone = '')
    {
        $opts = [];

        if ($date) {
            $opts = [
                'timeMin' => date("c", strtotime($date . '00:00:00')),
                'timeMax' => date("c", strtotime($date . '23:59:59')),
                'timeZone' => $timezone
            ];
        }

        $response = $this->service->events->listEvents($calendar_id, $opts);
        $values = $response->getItems();

        return $values;
    }

    /**
     * Check availability
     *
     * @param string $calendar_id - Calendar ID
     * @param string $start - Start date / time
     * @param string $end - End date / time
     * @param string $timezone - Timezone for request
     */
    public function checkAvailability(string $calendar_id, string $start, string $end, string $timezone)
    {
        // Format start / end times
        $start = date("c", strtotime($start));
        $end = date("c", strtotime($end));

        $freebusy_req = new Google_Service_Calendar_FreeBusyRequest();
        $freebusy_req->setTimeMin($start);
        $freebusy_req->setTimeMax($end);
        $freebusy_req->setTimeZone($timezone);

        $item = new Google_Service_Calendar_FreeBusyRequestItem();
        $item->setId($calendar_id);
        $freebusy_req->setItems([$item]);

        return $this->service->freebusy->query($freebusy_req)->calendars[$calendar_id]->busy;
    }
}
