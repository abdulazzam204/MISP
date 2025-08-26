#!/usr/bin/env python3
from pymisp import PyMISP
import urllib3, pprint
from keys import misp_url, misp_key

# --- CONFIGURE THESE ---
MISP_VERIFYCERT = False       # change to True if using valid SSL

# --- INIT ---
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
misp = PyMISP(misp_url, misp_key, MISP_VERIFYCERT)
org_name = 'abuse.ch'
tag = 'osint:source-type="block-or-filter-list"'

# --- SEARCH EVENTS FROM abuse.ch ---
events = misp.search_index(tags= ['osint:source-type="block-or-filter-list"'], org= f'!{org_name}', pythonify=True)

print(f"Found {len(events)} events with tag {tag}")

if not events:
    print(f"No events found for '{org_name}'.")
    exit
   
print(f"Found {len(events)} event(s). Updating distribution to 'All communities'...")

for event in events:
    '''
    event_id = event['id']
    current_dist = event['distribution']

    if current_dist == '1':
        print(f"Event {event_id} already set to 'All communities'. Skipping.")
        continue
    '''
    try:
        event.distribution = '3'
        updated = misp.update_event(event)
        print(f"✓ Updated Event {event.id} -> All communities")
    except Exception as e:
        print(f"✗ Failed to update Event {event.id}: {e}")

print("\nDone!")
