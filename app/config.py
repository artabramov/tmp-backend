class Config:
    #DEBUG = True

    LOG_FILENAME = '/var/log/echidna/echidna.log'
    LOG_FORMAT = '[%(asctime)s] %(method)s [%(url)s] %(levelname)s [%(name)s in %(filename)s, line %(lineno)d: "%(message)s"]'
    LOG_ROTATE_WHEN = 'H'
    LOG_BACKUP_COUNT = 24

    # dialect+driver://username:password@host:port/database
    #SQLALCHEMY_DATABASE_URI = 'postgresql+psycopg2://postgres:postgres@host.docker.internal:5432/postgres'
    SQLALCHEMY_DATABASE_URI = 'mysql+mysqlconnector://admin:admin@host.docker.internal:3306/echidna'
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    MONGO_URI = 'mongodb://user:pass@host.docker.internal:27017/echidna?authSource=admin'

    CELERY_BROKER_URL = 'amqp://guest:guest@host.docker.internal:5672//'
    CELERY_RESULT_BACKEND = 'redis://host.docker.internal:6379/0'
    CELERY_TASK_LIST = ['app.tasks']
    CELERY_RESULT_EXPIRES = 30
    CELERY_TASK_ROUTES = {
        'app.*': {'queue': 'user'}
    }

    USER_PASSWORD_SALT = 'paSS-$alt!'
    USER_AUTHCODE_SALT = 'aUTH-$alt?'