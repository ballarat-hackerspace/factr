from flask import Flask, request, jsonify, Response, make_response
from json import dumps

from generate_sentence import generate_sentence

app = Flask(__name__)


@app.route('/create_sentence', methods=["GET", "POST", "OPTIONS"])
def just_get_it_working():

    if request.method == 'OPTIONS':
        resp = app.make_default_options_response()
        resp.headers['Access-Control-Allow-Origin'] = '*'
        resp.headers['Access-Control-Allow-Headers'] = 'Content-Type'
        return resp


    results = []
    for object in request.json:
        results.append({"text": generate_sentence(object)})
    
    #resp = jsonify(results)
    resp = make_response(dumps(results))
    resp.headers['Access-Control-Allow-Origin'] = '*'
    resp.headers['Access-Control-Allow-Headers'] = 'Content-Type'
    
    return resp

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')
