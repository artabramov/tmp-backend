from app import app, log
from flask import request
from app.core.error_handler import error_handler
from app.core.response_format import response_format

from app import db
from app.models.group import Group

@app.route('/api/group/', methods=['POST'])
def post_group():

    group = Group(1, 'private', 'groupname')
    db.session.add(group)
    db.session.flush()

    db.session.commit()

    return response_format({
        'code': 200, 
        'error': 'OK',
        'data': {
        },
    })