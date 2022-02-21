from flask import Flask, request
from celery import Celery
from flask_sqlalchemy import SQLAlchemy
from flask_pymongo import PyMongo
from .config import Config
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

celery = Celery(
    broker=app.config['CELERY_BROKER_URL'],
    backend=app.config['CELERY_RESULT_BACKEND'],
    include=app.config['CELERY_TASK_LIST'],
)
celery.conf.task_routes = app.config['CELERY_TASK_ROUTES']
celery.conf.result_expires = app.config['CELERY_RESULT_EXPIRES']

# routes
from app.routes import hello
from app.routes import migrate
from app.routes import user_routes
from app.routes import post_routes
from app.routes import group_routes
