<?php

class ASEANEventsMapWidget {

    public $title = 'ASEAN + Japan Events Map';
    public $render = 'AseanMap';
    public $width = 5;
    public $height = 10;
    public $params = array(
        //'event_info' => 'World map based on the countries with infections.',
        //'type' => 'Type of data used for the widget (confirmed, death, recovered).',
        //'logarithmic' => 'Use a log10 scale for the graph (set via 0/1).'
    );
    public $description = 'Widget mapping cyber threats within ASEAN countries and Japan.';
    public $placeholder = '';

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

    public function handler($user, $options = array()) {
        $this->Event = ClassRegistry::init('Event');
        
        # fetch events (events that aren't tagged with block-or-filter-list tag)
        $params = [
            'tags' => ['!osint:source-type="block-or-filter-list"'],
            'order' => 'id'
        ];
        $eventIds = $this->Event->filterEventIds($user, $params);
        $events = $this->Event->fetchEvent($user, ['idList' => $eventIds, 'includeAllTags' => true]);
        //$events = $this->Event->fetchEvent($user, ['eventid' => 1799]);
        //$events = $this->Event->fetchEvent($user, ['tags' => '!osint:source-type="block-or-filter-list"', 'includeAllTags' => true]);
        //echo print_r($events);
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