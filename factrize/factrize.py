from flask import Flask, request, jsonify

from generate_sentence import generate_sentence

app = Flask(__name__)


@app.route('/create_sentence', methods=["GET", "POST"])
def just_get_it_working():

    print(request.json)

    results = {"text": generate_sentence(request.json)}

    return jsonify(results)



if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')
