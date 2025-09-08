<?php

class ASEANEventsMapWidget {

    public $title = 'ASEAN + Japan Events Map';
    public $render = 'AseanMap';
    public $width = 5;
    public $height = 10;
    public $params = array(
        'start_date' => 'Start date of events to look for, expressed in Y-m-d format (e.g. 2012-10-01)',
    );
    public $description = 'Widget mapping cyber threats within ASEAN countries and Japan.';
    public $placeholder = '{
    "start_date": "2015-01-01"
}';

    public $allowedTagsCountryCode = [
        //'misp-galaxy:region=\"035 - South-eastern Asia\"' => ['ID','BN','KH','LA','MY','MM','PH','SG','TH','VN'],
        'misp-galaxy:country="indonesia"' => 'ID',
        'misp-galaxy:country="japan"' => 'JP',
        'misp-galaxy:country="brunei"' => 'BN',
        'misp-galaxy:country="cambodia"' => 'KH',
        'misp-galaxy:country="laos"' => 'LA',
        'misp-galaxy:country="malaysia"' => 'MY',
        'misp-galaxy:country="myanmar"' => 'MM',
        'misp-galaxy:country="philippines"' => 'PH',
        'misp-galaxy:country="singapore"' => 'SG',
        'misp-galaxy:country="thailand"' => 'TH',
        'misp-galaxy:country="vietnam"' => 'VN',
    ];

    public $aseanCountryCodes = ['ID','BN','KH','LA','MY','MM','PH','SG','TH','VN'];

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
        
        # fetch events (events that aren't tagged with block-or-filter-list tag)
        $timeCondition = $this->timeConditions($options);
        $params = [
            'tags' => ['!osint:source-type="block-or-filter-list"'],
            'from' => $timeCondition,
            'order' => 'id',
        ];
        $eventIds = $this->Event->filterEventIds($user, $params);
        $events = $this->Event->fetchEvent($user, ['idList' => $eventIds, 'includeAllTags' => true]);
        # initialize data array
        $data = [];
        foreach ($this->allowedTagsCountryCode as $country) {
            $data[$country] = 0;
        }

        # search through each event tag to search for asean countries + japan
        foreach ($events as $event) {
            if (!empty($event['EventTag'])) {
                foreach ($event['EventTag'] as $tagObj) {
                    $tag = $tagObj['Tag']['name'];
                    # if asean tag found add 1 to each asean country
                    if($tag == 'misp-galaxy:region="035 - South-eastern Asia"') {
                        foreach ($this->aseanCountryCodes as $aseanCountry) {
                            $data[$aseanCountry] += 1;
                        }
                    }
                    # if country tag found add 1 to corresponding country
                    if(array_key_exists($tag, $this->allowedTagsCountryCode)) {
                        $data[$this->allowedTagsCountryCode[$tag]] += 1;
                    }
                }
            }
        }

        # format output for render
        $data = ['data' => $data];
        $data['scope'] = 'Events';
        $data['colour_scale'] = json_encode(['#F08080', '#8B0000']);
        return $data;
    }

}