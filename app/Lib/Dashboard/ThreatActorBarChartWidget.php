<?php

class ThreatActorBarChartWidget {
    public $title = 'Threat Actors Bar Chart';
    public $render = 'BarChartJs';
    public $width = 5;
    public $height = 10;
    public $params = array(
        'limit' => 'Limits how many attack pattern to display, 0 to disable limit. (default = 10)',
        'start_date' => 'Start date of events to look for, expressed in Y-m-d format (e.g. 2012-10-01)',
        'logarithmic' => 'Use a log10 scale for the graph. (0/1, default = 0).'
    );
    public $description = 'Show a bar chart of threat actors based on event.';
    public $placeholder = '{
    "limit" : "5",
    "start_date" : "2015-01-01",
    "logartihmic" : "1"
}';

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

    public function handler($user, $options = array()) {
        $this->Event = ClassRegistry::init('Event');
        $this->Galaxy = ClassRegistry::init('Galaxy');

        $limit = isset($options['limit']) ? (int)$options['limit'] : 10;
        $isLogarithmic = ($options['logarithmic'] === '1') ? true : false;
        $timeCondition = $this->timeConditions($options);
        $includeid = ($options['include_mitre_attack_id'] === '1') ? true : false;

        # fetch events
        $params = [
            'tags' => ['!osint:source-type="block-or-filter-list"'],
            'from' => $timeCondition,
            'order' => 'id'
        ];
        $eventIds = $this->Event->filterEventIds($user, $params);
        $events = $this->Event->fetchEvent($user, ['idList' => $eventIds, 'includeAllTags' => true]);

        $data=[];
    
        # go through each event and go through tags
        foreach ($events as $event) {
            if(!empty($event['EventTag'])) {
                foreach ($event['EventTag'] as $tagObj) {
                    $tag = $tagObj['Tag']['name'];
                    # if tag contains 'misp-galaxy:mitre-attack-pattern='
                    if (str_contains(strtolower($tag),'threat-actor')){
                        $threatActor = trim(explode('"', $tag)[1]);
                        if(!isset($data[$threatActor])) {
                            $data[$threatActor] = 0;
                        }
                        $data[$threatActor] += 1;
                    }
                }
            }
        }
        
        # limit the attack patterns shown
        arsort($data);
        if ($limit != 0) {
            $data = array_slice($data,0,$limit,true);
        }

        # calculate logarithmic values
        if ($isLogarithmic) {
            $logarithmic = [];
            foreach ($data as $k => $v) {
                if ($v == 0) {
                    $value = 0;
                } else if ($v <= 1) {
                    $value = 0.2;
                } else {
                    $value = log10($v);
                }
                $logarithmic[$k] = $value;
            }
        }
        
        $data = [
            'data' => $data,
            'logarithmic' => $logarithmic,
            'axis' => [
                'y' => 'Threat Actors'
            ],
        ];

        /*
        $data = [
            'data' => [
                'APT28' => 150,
                'Lazarus Group' => 120,
                'FIN7' => 95,
                'TA505' => 60,
                'Charming Kitten' => 45,
                'APT41' => 30,
                'EvilCorp' => 20,
                'DragonOK' => 15
            ],
            /*
            'colours' => [
                'APT28' => '#FF4C4C',       // Red
                'Lazarus Group' => '#4C6FFF', // Blue
                'FIN7' => '#4CFF6F',        // Green
                'TA505' => '#FFC24C',       // Orange
                'Charming Kitten' => '#FF4CFF', // Pink
                'APT41' => '#8C4CFF',       // Purple
                'EvilCorp' => '#4CFFFF',    // Cyan
                'DragonOK' => '#AAAAAA'     // Gray
            ],
            
            'logarithmic' => [
                'APT28' => round(log10(150), 2),         // ~2.18
                'Lazarus Group' => round(log10(120), 2), // ~2.08
                'FIN7' => round(log10(95), 2),           // ~1.98
                'TA505' => round(log10(60), 2),          // ~1.78
                'Charming Kitten' => round(log10(45), 2),// ~1.65
                'APT41' => round(log10(30), 2),          // ~1.48
                'EvilCorp' => round(log10(20), 2),       // ~1.30
                'DragonOK' => round(log10(15), 2)        // ~1.18
            ],
            
            'axis' => [
                'y' => 'Threat Actors'
            ],
            'output_decorator' => '' // leave empty if you don't need a "%" sign
        ];
        */

        return $data;
    }
}