from flask import Flask, request
from celery import Celery
from flask_sqlalchemy import SQLAlchemy
from flask_pymongo import PyMongo
from .config import Config
#import os, pwd, grp
#import logging
from app.core.log_wrapper import log_wrapper


app = Flask(__name__)
app.config.from_object(Config)
log = log_wrapper(app)
db = SQLAlchemy(app)
mongo = PyMongo(app)

"""
def make_celery():
    celery = Celery(
        broker=app.config['CELERY_BROKER_URL'],
        backend=app.config['CELERY_RESULT_BACKEND'],
        include=app.config['CELERY_TASK_LIST'],
    )

    celery.conf.task_routes = app.config['CELERY_TASK_ROUTES']
    celery.conf.result_expires = app.config['CELERY_RESULT_EXPIRES']

    TaskBase = celery.Task

    class ContextTask(TaskBase):
        abstract = True

        def __call__(self, *args, **kwargs):
            with app.app_context():
                return TaskBase.__call__(self, *args, **kwargs)

    celery.Task = ContextTask
    return celery
celery = make_celery()
"""

#celery = Celery(
#    broker=app.config['CELERY_BROKER_URL'],
#    backend=app.config['CELERY_RESULT_BACKEND'],
#    include=app.config['CELERY_TASK_LIST'],
#)
#celery.conf.task_routes = app.config['CELERY_TASK_ROUTES']
#celery.conf.result_expires = app.config['CELERY_RESULT_EXPIRES']


"""
if not os.path.isfile(app.config['LOG_FILENAME']):
    open(app.config['LOG_FILENAME'], 'a').close()
    uid = pwd.getpwnam('www-data').pw_uid
    gid = grp.getgrnam('root').gr_gid
    os.chown(app.config['LOG_FILENAME'], uid, gid)

class ContextualFilter(logging.Filter):
    def filter(self, message):
        message.url = request.url
        message.method = request.method
        return True

while app.logger.hasHandlers():
    app.logger.removeHandler(app.logger.handlers[0])

handler = logging.handlers.TimedRotatingFileHandler(
    filename=app.config['LOG_FILENAME'], 
    when=app.config['LOG_ROTATE_WHEN'], 
    backupCount=app.config['LOG_BACKUP_COUNT'])
handler.setFormatter(logging.Formatter(app.config['LOG_FORMAT']))
app.logger.addHandler(handler)
context_provider = ContextualFilter()
app.logger.addFilter(context_provider)
log = app.logger
"""

# routes
from app.routes import hello
#from app.routes import migrate
#from app.routes import user_routes
#from app.routes import post_routes
#from app.routes import group_routes
