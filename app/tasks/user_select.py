import werkzeug
from .. import app, log, celery
from ..models.user import User
from ..models.user_meta import UserMeta

@celery.task(name='app.user_select', ignore_result=False)
def user_select(user_id):
    try:
        user = User.query.filter_by(id=user_id).first()
    except Exception as e:
        log.critical(e)
        return {
            'code': 500, 
            'error': 'Internal Server Error', 
            'data': {},
        }

    if not user:
        return {
            'code': 404, 
            'error': 'user not found', 
            'data': {},
        }

    return {
        'code': 200, 
        'error': '',
        'data': {
            'user': {
                'id': user.id,
                'user_email': user.user_email,
            }
        }
    }
