FROM python:3.10
RUN apt-get update
RUN mkdir /echidna
WORKDIR /echidna

RUN mkdir /var/log/celery
RUN chown -R www-data /var/log/celery

RUN mkdir /var/log/echidna
RUN chown -R www-data /var/log/echidna

# apache
RUN apt-get install -y apache2 apache2-dev
COPY ./src/.htaccess ./
RUN a2enmod rewrite
RUN apt-get clean
COPY ./src/000-default.conf ./
RUN mv --force ./000-default.conf /etc/apache2/sites-available/000-default.conf

# venv & requirements
RUN python3.10 -m venv ./venv
RUN ./venv/bin/pip3.10 install mod_wsgi
RUN ln -s /echidna/venv/lib/python3.10/site-packages/mod_wsgi/server/mod_wsgi-*.so /echidna/venv/lib/python3.10/site-packages/mod_wsgi/server/mod_wsgi.so
#COPY ./src/requirements.txt ./
#RUN ./venv/bin/pip3.10 install --no-cache-dir -r ./requirements.txt
RUN ./venv/bin/pip3.10 install Flask==2.0.2
RUN ./venv/bin/pip3.10 install SQLAlchemy==1.4.31
RUN ./venv/bin/pip3.10 install celery==5.2.3
RUN ./venv/bin/pip3.10 install Flask-Celery==2.4.3
RUN ./venv/bin/pip3.10 install Flask-SQLAlchemy==2.5.1
RUN ./venv/bin/pip3.10 install Flask-PyMongo==2.3.0
RUN ./venv/bin/pip3.10 install mysql-connector-python==8.0.28
#RUN ./venv/bin/pip3.10 install psycopg2==2.9.2
RUN ./venv/bin/pip3.10 install -U "celery[redis]"

# echidna
COPY ./echidna/ ./
EXPOSE 80
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# celery
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
