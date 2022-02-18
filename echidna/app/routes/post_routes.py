from app import app, log
from app import mongo
from flask import request
from app.core.error_handler import error_handler
from app.core.response_format import response_format


@app.route('/api/post/', methods=['POST'])
def document_post():

    post = {
        'post_status': 'todo',
        'post_content': 'post content'
    }
    post_id = mongo.db.posts.insert_one(post).inserted_id

    return response_format({
        'code': 200, 
        'error': 'OK',
        'data': {
            'post_id': str(post_id)
        },
    })