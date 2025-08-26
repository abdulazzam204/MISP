#!/usr/bin/env python3
from pymisp import PyMISP
import urllib3
from keys import misp_url, misp_key

# --- CONFIGURE THESE ---
MISP_VERIFYCERT = False       # change to True if using valid SSL
TAG = 'osint:source-type="block-or-filter-list"'

# --- INIT ---
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
misp = PyMISP(misp_url, misp_key, MISP_VERIFYCERT)

# --- SEARCH EVENTS FROM abuse.ch ---
events = misp.search_index(org='abuse.ch')

print(f"Found {len(events)} events from abuse.ch")

for e in events:
    event_id = e['id']
    event_uuid = e['uuid']
    event_info = e['info']
    existing_tags = [t['name'] for t in e.get('Tag', [])]

    if TAG in existing_tags:
        print(f"Skipping Event {event_id} ({event_info}) → already tagged")
        continue

    try:
        misp.tag(event_uuid, TAG)
        print(f"Tagged Event {event_id} ({event_info}) with {TAG}")
    except Exception as err:
        print(f"  [!] Failed to tag event {event_id}: {err}")
