import pymisp 
from keys import misp_key, misp_url
import requests, json, time, urllib3
from pprint import pp as pprint
import keywords as kw

feedUrl = "https://misp.rosti.bin.re/downloads/misp/"
manifest = requests.get(f"{feedUrl}manifest.json").json()
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
misp = pymisp.PyMISP(misp_url, misp_key, False)

#keywords = ["japan","indonesia","malaysia","brunei","cambodia","laos","myanmar","philippines","singapore","thailand","vietnam","asean","southeast asia","south east asia"]
keywords = kw.Keywords.keywords

#print(type(manifest))

print(f"Importing events from: {feedUrl}\n======================")

start = time.perf_counter()
numEventsScanned = 0

for eventId, eventMeta in manifest.items():
    eventData = requests.get(f"{feedUrl}{eventId}.json").json()
    eventInfo = eventData["Event"]["info"].lower()
    foundWord = False

    for keyword in keywords:
        if(foundWord):
            break
        if keyword.lower() in eventInfo:
            foundWord = True
            print(f"Importing: {eventInfo}")

            # create misp event
            try:
                print("Trying to create event")
                event = pymisp.MISPEvent()
                event.from_dict(**eventData["Event"])
                print("Succesfully created event")
                #pprint(event.to_json())
            except:
                print("Failed to create event")
                print("-------------------------")
                break

            # add country / region galaxy cluster to event
            try:
                print("Adding tag")
                region = kw.Keywords.to_country(keyword)
                if(keyword == "asean" or keyword == "southeast asia" or keyword == "south east asia"):
                    tag_value = f"misp-galaxy:region=\"{region}\""
                else: 
                    tag_value = f"misp-galaxy:country=\"{region}\""
                event.add_tag(tag_value)
                print("Successfully added tag")
            except:
                print("Failed to add tag")
                print("-------------------------")
                break

            # import event to misp
            try:
                # check if event already exist
                print("Checking if event already exists")
                exists = misp.event_exists(eventData["Event"]["uuid"])
                if exists:
                    print("Event exists, updating event")
                    misp.update_event(event)
                    print("Succesfully updated event")
                else:
                    print("Event doesn't exist, uploading new event")
                    misp.add_event(event)
                    print("Successfully uploaded new event")
            except:
                print("Failed to upload event")
                print("-------------------------")
                break

            print("-------------------------")


    numEventsScanned += 1
        
end = time.perf_counter()
print(f"Script took {end - start:.2f} seconds")
print(f"Events scanned: {numEventsScanned}")