from app import app
from app import db
import enum
import werkzeug
import re
import hashlib
from app.models.base_model import BaseModel


class UserStatus(enum.Enum):
    pending = 1
    approved = 2
    trash = 3


class User(BaseModel):
    EMAIL_REGEX = re.compile(r"^[a-z0-9._-]{2,122}@[a-z0-9._-]{2,122}\.[a-z]{2,10}$")
    NAME_REGEX = re.compile(r"^[^\s]{1}[a-zA-Z0-9 ]{2,38}[^\s]{1}$")
    PASSWORD_REGEX = re.compile(r"(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!#%*?&]{6,40}$")
    PASSWORD_ATTEMPTS_LIMIT = 10
    PASSWORD_SALT = 'password-salt'

    __tablename__ = 'users'
    __user_password = ''
    restored_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    authed_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    user_status = db.Column(db.Enum(UserStatus), nullable=False)
    user_email = db.Column(db.String(255), nullable=False, index=True, unique=True)
    user_name = db.Column(db.String(40), nullable=False)
    password_hash = db.Column(db.String(64), nullable=False)
    password_attempts = db.Column(db.SmallInteger, nullable=False)

    user_token = db.relationship('UserToken', backref='users')
    user_meta = db.relationship('UserMeta', backref='users')

    def __init__(self, user_email, user_password, user_name, user_status='pending'):
        self.user_status = user_status
        self.user_email = user_email
        self.user_name = user_name
        self.user_password = user_password
        self.password_attempts = 0

    @property
    def user_password(self):
        return self.__user_password

    @user_password.setter
    def user_password(self, value):
        if not value:
            raise werkzeug.exceptions.BadRequest('user_password is empty')
        elif not self._is_user_password_correct(value):
            raise werkzeug.exceptions.BadRequest('user_password is incorrect')
        self.__user_password = value
        self.password_hash = self._get_password_hash(value)

    def _is_user_email_correct(self, user_email):
        return True if self.EMAIL_REGEX.match(user_email) else False

    def _is_user_password_correct(self, user_password):
        return True if re.search(self.PASSWORD_REGEX, user_password) else False

    def _is_user_name_correct(self, user_name):
        return True if self.NAME_REGEX.match(user_name) else False

    def _get_password_hash(self, user_password):
        user_password = user_password + self.PASSWORD_SALT
        encoded_pass = user_password.encode()
        hash_obj = hashlib.sha256(encoded_pass)
        return hash_obj.hexdigest()

    @db.validates('user_email')
    def validate_user_email(self, key, value):
        if key == 'user_email' and not value:
            raise werkzeug.exceptions.BadRequest('user_email is empty')
        elif key == 'user_email' and not self._is_user_email_correct(value):
            raise werkzeug.exceptions.BadRequest('user_email is incorrect')
        return value
        
    @db.validates('user_name')
    def validate_user_name(self, key, value):
        if key == 'user_name' and not value:
            raise werkzeug.exceptions.BadRequest('user_name is empty')
        elif key == 'user_name' and not self._is_user_name_correct(value):
            raise werkzeug.exceptions.BadRequest('user_name is incorrect')
        return value


@db.event.listens_for(User, 'before_insert')
def before_insert_user(mapper, connect, user):
    if User.query.filter_by(user_email=user.user_email).first():
        raise werkzeug.exceptions.BadRequest('user_email already exists')
