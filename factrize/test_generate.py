#WTF A test in GovHack?
import pytest
from generate_sentence import generate_sentence
from datetime import datetime
import time


def get_timestamp(dt=None):
    if dt is None:
        dt = datetime.now()
    return int(time.mktime(dt.timetuple()))


tests = [
    {"category": "light", "attributes": [{"name": "watts", "value": 2100}, {"name": "turnon", "value": "6pm"}],
     "datetime": 0},
    {"category":"light", "attributes":[{"name":"watts", "value": 2100}, {"name":"turnon", "value": "6pm"}], "datetime":get_timestamp()}
]


def test_generate():
    # Don't worry, I'm only testing the code runs at all..*[]:
    for j in range(10):
        for input_data in tests:
            print(generate_sentence(input_data))