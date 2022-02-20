from .. import app, log, db, celery, mongo
import werkzeug

@celery.task(name='app.post_insert', time_limit=10, ignore_result=False)
def post_insert(post_status, post_content):
    try:
        post = {
            'post_status': 'todo',
            'post_content': 'post content'
        }
        mongo.db.posts.insert_one(post)

    except Exception as e:
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
