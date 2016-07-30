from flask import Flask, request, jsonify

from generate_sentence import generate_sentence

app = Flask(__name__)


@app.route('/create_sentence')
def just_get_it_working():

    print(request.values)

    results = {"text": generate_sentence(request.values)}

    return jsonify(results)



if __name__ == '__main__':
    app.run()
