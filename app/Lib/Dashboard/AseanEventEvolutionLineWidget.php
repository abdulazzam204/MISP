<?php

class AseanEventEvolutionLineWidget
{
    public $title = 'Evolution of ASEAN + Japan Events';
    public $render = 'MultiLineChart';
    public $width = 7;
    public $height = 6;
    public $description = 'A linechart of events created.';
    public $cacheLifetime = null;
    public $autoRefreshDelay = false;
    public $params = [
        'start_date' => 'Start date, expressed in Y-m-d format (e.g. 2012-10-01)',
        'cumulative' => '(default: 1), should the data counted cumulatively over time. (0/1)',
    ];

    public $placeholder = '{
    "start_date": "2015-01-01",
    "cumulative": "1"
}';

    private $Organisation = null;
    private $Event = null;

    private function timeConditions($options)
    {
        if (!empty($options['start_date'])) {
            $condition = strtotime($options['start_date']);
        } else {
            $condition = strtotime('2012-10-01');
        }
        $datetime = new DateTime();
        $datetime->setTimestamp($condition);
        return $datetime->format('Y-m-d');
    }

    private function convertTimestamp($timestamp) {
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        return $datetime->format('Y-m');
    }

    public function handler($user, $options = array())
    {
        $this->Event = ClassRegistry::init('Event');
        $isCumulative = ($options['cumulative'] === '0') ? false : true;

        # fetch events
        $timeCondition = $this->timeConditions($options);
        $params = [
            'tags' => ['!osint:source-type="block-or-filter-list"'],
            'from' => $timeCondition,
            'order' => 'timestamp',
        ];
        $eventIds = $this->Event->filterEventIds($user, $params);
        $events = $this->Event->fetchEvent($user, ['idList' => $eventIds]);
        /*
        foreach ($events as $event) {
            echo print_r($event['Event']['timestamp'].' '.$event['Event']['date'].PHP_EOL);
        }
        */

        # extract timestamp from events and turn it into Y-m format
        $raw = [];
        foreach ($events as $event) {
            $raw[] = [
                'date' => substr($event['Event']['date'],0,7).'-01'
            ];
        }
        //echo print_r($raw);

        # sort events by date
        usort($raw, [$this, 'sortByDate']);
        //echo print_r($raw);

        # create time periods
        $default_start_date = empty($raw) ? '2012-10-01' : ($raw[0]['date']);
        $start = new DateTime(empty($options['start_date']) ? $default_start_date : $options['start_date']);
        $end = new DateTime(date('Y-m') . '-01');
        $interval = DateInterval::createFromDateString('1 month');
        //echo print_r($start);
        //echo var_dump($end);
        //echo var_dump($interval);
        $period = new DatePeriod($start, $interval, $end);
        //echo var_dump($period);
        $raw_padded = [];
        foreach ($period as $dt) {
           $raw_padded[$dt->format('Y-m').'-01'] = 0;
        }
        //echo print_r($raw_padded);

        # loop through raw data and count events for each time period
        foreach ($raw as $eventMonth) {
            $raw_padded[$eventMonth['date']] += 1;
        }
        //echo print_r($raw_padded);

        # handle cumulative logic
        if($isCumulative) {
            $total = 0;
            foreach ($raw_padded as $date => $count) {
                $total += $count;
                $raw_padded[$date] = $total;
            }
        }

        # return data
        $data = [];
        foreach ($raw_padded as $date => $count) {
            $data['data'][] = [
                'Events' => (int)$count,
                'date' => $date
            ];
        }
        return $data;
    }

    private function sortByDate($a, $b) {
        if ($a['date'] > $b['date']) { 
            return 1;
        } else {
            return -1;
        }
        return 0;
    }
}
