from pymisp import PyMISP
from keys import misp_key, misp_url
import requests, json
from pprint import pp as pprint

feedUrl = "https://www.botvrij.eu/data/feed-osint"
manifest = requests.get(f"{feedUrl}/manifest.json").json()

keywords = ["japan","indonesia","malaysia","brunei","cambodia","laos","myanmar","philippines","singapore","thailand","vietnam"]

#print(type(manifest))

for eventId, eventMeta in manifest.items():
    eventData = requests.get(f"{feedUrl}/{eventId}").json()
    eventInfo = eventData["Event"]["info"].lower()

    if any(keyword.lower() in eventInfo for keyword in keywords):
        print(f"import: {eventInfo}")