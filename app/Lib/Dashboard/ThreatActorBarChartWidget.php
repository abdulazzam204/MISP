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
        $tagIds=[];
    
        # go through each event and go through tags
        foreach ($events as $event) {
            if(!empty($event['EventTag'])) {
                foreach ($event['EventTag'] as $tagObj) {
                    $tag = $tagObj['Tag']['name'];
                    $tagid = $tagObj['Tag']['id'];
                    # if tag contains 'misp-galaxy:mitre-attack-pattern='
                    if (str_contains(strtolower($tag),'threat-actor')){
                        $threatActor = trim(explode('"', $tag)[1]);
                        if(!isset($data[$threatActor])) {
                            $data[$threatActor] = 0;
                        }
                        $data[$threatActor] += 1;
                        if(!isset($tagIds[$threatActor]) || !in_array($tagid, $tagIds[$threatActor])) {
                            $tagIds[$threatActor][] = $tagid;
                        }
                    }
                }
            }
        }
        
        # limit the attack patterns shown
        arsort($data);
        if ($limit != 0) {
            $data = array_slice($data,0,$limit,true);
        }
        $links=[];
        foreach ($data as $thrtActor => $v) {
            $link = '/events/index/searchextending:undefined/searchextended:undefined/searchtag:';
            foreach($tagIds[$thrtActor] as $tg) {
                $link = $link.$tg.'|';
            }
            $links[$thrtActor] = $link;
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
            'links' => $links,
            'logarithmic' => $logarithmic,
            'axis' => [
                'y' => 'Threat Actors'
            ],
        ];
        return $data;
    }
}