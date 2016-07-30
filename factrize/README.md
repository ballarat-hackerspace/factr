

Basic call:

```
curl -H "Content-Type: application/json" -X POST -d '{"category":"light", "attributes":[{"name":"watts", "value": 2100}, {"name":"turnon", "value": "6pm"}], "datetime":0}' http://127.0.0.1:5000/create_sentence
```