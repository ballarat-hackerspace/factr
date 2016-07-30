

Basic call:

```
curl -H "Content-Type: application/json" -X POST -d '{"category":"light", "attributes":[{"name":"watts", "value": 2100}, {"name":"turnon", "value": "6pm"}], "datetime":0}' http://127.0.0.1:5000/create_sentence
```


Basic information

Sentence generation is a hard problem, but by starting with basic sentence forms, and integrating artificial intelligence using natural language generation capability, we can start to engage more complex sentences and relationships between facts.
At present, the data focuses on a few simple cases, both with temporal and non-temporal data.

Language models will be used to help create new factoids from key fact forms, such as basic information ("That thing has this attribute") to more comparative ideas ("That is the largest thing in Australia" or "You'll soon come by the fifth thing you've seen today").
As the usage grows, we will also get a sense of which facts people are most interested in.

