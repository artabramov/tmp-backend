from flask import make_response, jsonify

def response_format(message):
    response = make_response(
        jsonify(
            {
                'error': message['error'],
                'data': message['data'],
            }
        ),
        message['code'],
    )
    response.headers['Content-Type'] = 'application/json'
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Server'] = 'noserver'
    return response