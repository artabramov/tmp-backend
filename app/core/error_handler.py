from app import app, log
from app.core.response_format import response_format


@app.errorhandler(Exception)
def error_handler(e):
    log.critical(e)
    result = {
        'code': 500,
        'error': 'Internal Server Error',
        'data': {},
    }
    return response_format(result)
