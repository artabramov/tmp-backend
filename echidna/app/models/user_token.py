from app import db
import secrets


class UserToken(db.Model):
    __tablename__ = 'users_tokens'
    id = db.Column(db.BigInteger, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    deleted_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    expired_date = db.Column(db.DateTime(timezone=False), nullable=False)
    user_id = db.Column(db.BigInteger, db.ForeignKey('users.id'), index=True, nullable=False)
    refresh_token = db.Column(db.String(128), index=True, unique=True, nullable=False)
    remote_addr = db.Column(db.String(48), index=True, nullable=False)
    user_agent = db.Column(db.Text, nullable=False)

    def __init__(self, user_id, remote_addr, user_agent):
        self.user_id = user_id
        self.expired_date = '1970-01-01 00:00:00'
        self.refresh_token = secrets.token_urlsafe(80)
        self.remote_addr = remote_addr
        self.user_agent = user_agent
