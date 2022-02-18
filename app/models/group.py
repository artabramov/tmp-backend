from app import app
from app import db
import enum
import werkzeug
from app.models.base_model import BaseModel


class GroupStatus(enum.Enum):
    private = 1
    public = 2
    trash = 3


class Group(BaseModel):
    __tablename__ = 'groups'
    #id = db.Column(db.BigInteger, primary_key=True)
    #created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    #updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    #deleted_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    user_id = db.Column(db.BigInteger, db.ForeignKey('users.id'), index=True)
    group_status = db.Column(db.Enum(GroupStatus))
    group_name = db.Column(db.String(40))


    def __init__(self, user_id, group_status, group_name):
        self.user_id = user_id
        self.group_status = group_status
        self.group_name = group_name

    def _is_group_name_correct(self, group_name):
        return True if group_name.isalnum() and 2 < len(group_name) < 40 else False
        
    @db.validates('group_name')
    def validate_group_name(self, key, value):
        if key == 'group_name' and not value:
            raise werkzeug.exceptions.BadRequest('group_name is empty')
        elif key == 'group_name' and not self._is_group_name_correct(value):
            raise werkzeug.exceptions.BadRequest('group_name is incorrect')
        return value
