<?php

class AttackPatternPieChartWidget {
    public $title = 'MITRE Attack Pattern Distribution Pie Chart';
    public $render = 'PieChart';
    public $width = 5;
    public $height = 10;
    public $params = array(
        'limit' => 'Limits how many attack pattern to display, 0 to disable limit. (default = 10)',
        'start_date' => 'Start date of events to look for, expressed in Y-m-d format (e.g. 2012-10-01)',
        'include_mitre_attack_id' => 'Whether to seperate attack patterns by its ATT&CK id. (0/1, default=0)'
    );
    public $description = 'Show a pie chart of attack pattern distribution of events, based on event tags.';
    public $placeholder = '{
    "limit": "5",
    "start_date": "2015-01-01"
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
                    if (str_contains(strtolower($tag),'attack-pattern')){
                        $attackPattern = explode('"', $tag)[1];
                        if(!$includeid) {
                            $attackPattern = trim(explode('-', $attackPattern)[0]);
                        }
                        if(!isset($data[$attackPattern])) {
                            $data[$attackPattern] = 0;
                        }
                        $data[$attackPattern] += 1;
                    }
                }
            }
        }
        
        # limit the attack patterns shown
        arsort($data);
        if ($limit != 0) {
            $data = array_slice($data,0,$limit,true);
        }

        $data = [
            'data' => $data
        ];
        return $data;
    }
}