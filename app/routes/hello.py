from app import app
#from app import log
from app.core.response_format import response_format

@app.route('/api/hello/')
def hello_world():
    #log.error('hello world error')
    result = {
        'code': 200, 
        'error': '',
        'data': {'response': 'hello, world!'},
    }   

    return response_format(result)
