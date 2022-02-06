from .. import app, log, db, celery
from ..models.user import User
from ..models.user_meta import UserMeta
from ..models.user_token import UserToken
import werkzeug

@celery.task(name='app.user_insert', time_limit=10, ignore_result=False)
def user_insert(user_email, user_password, user_name, remote_addr, user_agent):
    try:
        user = User(user_email, user_password, user_name)
        db.session.add(user)
        db.session.flush()

        user_token = UserToken(user.id, remote_addr, user_agent)
        db.session.add(user_token)
        db.session.flush()

        user_meta = UserMeta(user.id, 'meta_key', 'meta_value')
        db.session.add(user_meta)
        db.session.flush()

        db.session.commit()

    except werkzeug.exceptions.BadRequest as e:
        db.session.rollback()
        return {
            'code': e.code, 
            'error': e.description, 
            'data': {},
        }

    except Exception as e:
        db.session.rollback()
        log.critical(e)
        return {
            'code': 500, 
            'error': 'Internal Server Error',
            'data': {},
        }

    else:
        return {
            'code': 200, 
            'error': '', 
            'data': {'user': {'id': user.id}},
        }
