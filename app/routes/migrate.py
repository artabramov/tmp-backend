from app import app, db
from app.core.error_handler import error_handler
from app.core.response_format import response_format

@app.route('/api/v1/migrate')
def migrate():
    db.create_all()
    result = {
        'code': 200, 
        'error': '',
        'data': {},
    }   

    return response_format(result)
