from flask import request, has_request_context
import logging
import os
import pwd, grp

def log_wrapper(app):
    if not os.path.isfile(app.config['LOG_FILENAME']):
        open(app.config['LOG_FILENAME'], 'a').close()
        uid = pwd.getpwnam('www-data').pw_uid
        gid = grp.getgrnam('root').gr_gid
        os.chown(app.config['LOG_FILENAME'], uid, gid)

    #class ContextualFilter(logging.Filter):
    #    def filter(self, message):
    #        if has_request_context():
    #            message.url = request.url
    #            message.method = request.method
    #        else:
    #            message.url = '-'
    #            message.method = '-'
    #        return True

    while app.logger.hasHandlers():
        app.logger.removeHandler(app.logger.handlers[0])

    handler = logging.handlers.TimedRotatingFileHandler(
        filename=app.config['LOG_FILENAME'], 
        when=app.config['LOG_ROTATE_WHEN'], 
        backupCount=app.config['LOG_BACKUP_COUNT'])
    #handler.setFormatter(logging.Formatter(app.config['LOG_FORMAT']))
    app.logger.addHandler(handler)
    #context_provider = ContextualFilter()
    #app.logger.addFilter(context_provider)
    return app.logger
