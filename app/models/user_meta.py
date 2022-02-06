from app import db


class UserMeta(db.Model):
    __tablename__ = 'users_meta'
    __table_args__ = (db.UniqueConstraint('user_id', 'meta_key', name='users_meta_ukey'),)
    id = db.Column(db.BigInteger, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    user_id = db.Column(db.BigInteger, db.ForeignKey('users.id'), index=True)
    meta_key = db.Column(db.String(20), index=True, nullable=False)
    meta_value = db.Column(db.String(255), nullable=True)

    def __init__(self, user_id, meta_key, meta_value):
        self.user_id = user_id
        self.meta_key = meta_key
        self.meta_value = meta_value

    @db.validates('meta_value')
    def validate_meta_value(self, key, value):
        if key == 'meta_value' and not value:
            raise werkzeug.exceptions.BadRequest('meta_value is empty')
        elif key == 'meta_value' and len(value) > 255:
            raise werkzeug.exceptions.BadRequest('meta_value is incorrect')
        return value
