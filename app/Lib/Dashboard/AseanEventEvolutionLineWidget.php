<?php

class AseanEventEvolutionLineWidget
{
    public $title = 'Evolution of ASEAN + Japan Events';
    public $render = 'MultiLineChartNoTotal';
    public $width = 7;
    public $height = 6;
    public $description = 'A linechart of events created.';
    public $cacheLifetime = null;
    public $autoRefreshDelay = false;
    public $params = [
        'start_date' => 'Start date, expressed in Y-m-d format (e.g. 2012-10-01)',
        'cumulative' => 'Should the data be counted cumulatively over time. (0/1, default = 1)',
        'by_country' => 'Should the data be separated by country. (0/1, default = 0)'
    ];

    public $placeholder = '{
    "start_date": "2015-01-01",
    "cumulative": "1",
    "by_country" : "1"
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

    private function convertTimestamp($timestamp)
    {
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        return $datetime->format('Y-m');
    }

    public function handler($user, $options = array())
    {
        $this->Event = ClassRegistry::init('Event');
        $isCumulative = ($options['cumulative'] === '0') ? false : true;
        $byCountry = ($options['by_country'] === '0') ? false : true;

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

        # extract date from events and turn it into Y-m format
        $raw = [];
        foreach ($events as $event) {
            $raw[] = [
                'date' => substr($event['Event']['date'], 0, 7) . '-01',
                'country' => $this->searchCountry($event)
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
        $period = new DatePeriod($start, $interval, $end);
        $raw_padded = [];
        foreach ($period as $dt) {
            $raw_padded[$dt->format('Y-m') . '-01']['total'] = 0;
            foreach ($this->countryTags as $country) {
                $raw_padded[$dt->format('Y-m') . '-01']['countries'][$country] = 0;
            }
        }
        //echo print_r($raw_padded);

        # loop through raw data and count events for each time period
        foreach ($raw as $event) {
            $raw_padded[$event['date']]['total'] += 1;
            foreach ($event['country'] as $country) {
                $raw_padded[$event['date']]['countries'][$country] += 1;
            }
        }

        //echo print_r($raw_padded);

        # handle cumulative logic
        if ($isCumulative) {
            $total = 0;
            $totalByCountry = [];
            foreach($this->countryTags as $country) {
                $totalByCountry[$country] = 0;
            }
            foreach ($raw_padded as $date => $dataPoint) {
                $total += $dataPoint['total'];
                $raw_padded[$date]['total'] = $total;
                foreach ($dataPoint['countries'] as $country => $count) {
                    $totalByCountry[$country] += $count;
                    $raw_padded[$date]['countries'][$country] = $totalByCountry[$country];
                }
            }
        }

        # return data
        $data = [];
        foreach ($raw_padded as $date => $dataPoint) {
            $temp = [
                'Events' => (int) $dataPoint['total'],
                'date' => $date
            ];
            if($byCountry) {
                foreach ($dataPoint['countries'] as $country => $count) {
                    $temp[$country] = $count;
                }
            } 
            $data['data'][] = $temp;
        }
        return $data;
    }

    private function sortByDate($a, $b)
    {
        if ($a['date'] > $b['date']) {
            return 1;
        } else {
            return -1;
        }
        return 0;
    }

    private function searchCountry($event)
    {
        $countries = [];
        if (!empty($event['EventTag'])) {
            foreach ($event['EventTag'] as $tagObj) {
                $tag = $tagObj['Tag']['name'];
                if ($tag == 'misp-galaxy:region="035 - South-eastern Asia"') {
                    $countries = array_merge($this->aseanCountries, $countries);
                }
                if (array_key_exists($tag, $this->countryTags)) {
                    $countries[] = $this->countryTags[$tag];
                }
            }
        }
        array_unique($countries);
        return $countries;
    }

    public $aseanCountries = ['Indonesia', 'Brunei', 'Cambodia', 'Laos', 'Malaysia', 'Myanmar', 'Philippines', 'Singapore', 'Thailand', 'Vietnam'];

    public $countryTags = [
        //'misp-galaxy:region=\"035 - South-eastern Asia\"' => ['ID','BN','KH','LA','MY','MM','PH','SG','TH','VN'],
        'misp-galaxy:country="indonesia"' => 'Indonesia',
        'misp-galaxy:country="japan"' => 'Japan',
        'misp-galaxy:country="brunei"' => 'Brunei',
        'misp-galaxy:country="cambodia"' => 'Cambodia',
        'misp-galaxy:country="laos"' => 'Laos',
        'misp-galaxy:country="malaysia"' => 'Malaysia',
        'misp-galaxy:country="myanmar"' => 'Myanmar',
        'misp-galaxy:country="philippines"' => 'Philippines',
        'misp-galaxy:country="singapore"' => 'Singapore',
        'misp-galaxy:country="thailand"' => 'Thailand',
        'misp-galaxy:country="vietnam"' => 'Vietnam',
    ];
}
