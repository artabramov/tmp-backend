from app import app, log
from flask import request
from app.core.error_handler import error_handler
from app.core.response_format import response_format
from app.tasks.user_select import user_select
from app.tasks.user_insert import user_insert

@app.route('/api/user/', methods=['POST'])
def user_post():
    log.error('user route')
    try:
        async_result = user_insert.apply_async(args=[
            request.args.get('user_email', None),
            request.args.get('user_password', None),
            request.args.get('user_name', None),
            request.remote_addr,
            request.user_agent.string,
        ])

    except Exception as e:
        log.critical(e)
        return response_format({
            'code': 500,
            'error': 'Internal Server Error',
            'data': {},
        })

    try:
        result = async_result.get(timeout=10)
    except Exception as e:
        log.critical(e)
        return response_format({
            'code': 522,
            'error': 'Connection Timed Out',
            'data': {},
        })


    #if result['code'] == 200:
    #    from app.models.user_token import UserToken
    #    from app import db
    #    user_token = UserToken(result['data']['user']['id'], 'remote_addr', 'user_agent')
    #    db.session.add(user_token)
    #    db.session.commit()


    return response_format(result)


@app.route('/api/user/<int:user_id>', methods=['GET'])
def user_get(user_id):
    try:
        result = user_select.apply_async(args=[user_id]).get(timeout=10)
    except Exception as e:
        log.critical(e)
        result = {
            'code': 522, 
            'error': 'Connection Timed Out',
            'data': {},
        }   

    return response_format(result)
