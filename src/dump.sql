SET sql_mode = '';
CREATE TABLE IF NOT EXISTS project.users (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_status VARCHAR(20)  NOT NULL, # pending | approved | trash
    user_token  VARCHAR(80)  NOT NULL,
    user_email  VARCHAR(255) NOT NULL,
    user_name   VARCHAR(128) NOT NULL,
    user_hash   VARCHAR(40)  NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY user_status (user_status),
    UNIQUE  KEY user_token  (user_token),
    UNIQUE  KEY user_email  (user_email),
            KEY user_name   (user_name),
            KEY user_hash   (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS project.user_meta (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
    FOREIGN KEY (user_id) REFERENCES project.users (id) ON DELETE CASCADE,
            KEY meta_key    (meta_key),
            KEY meta_value  (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TRIGGER project.user_meta_insert
AFTER INSERT 
ON project.user_meta 
FOR EACH ROW
INSERT INTO project.user_events(user_id, parent_type, parent_id, event_name) VALUES(NEW.user_id, 'user_meta', NEW.id, 'INSERT');

CREATE TRIGGER project.user_meta_update
AFTER UPDATE 
ON project.user_meta 
FOR EACH ROW
INSERT INTO project.user_events(user_id, parent_type, parent_id, event_name) VALUES(OLD.user_id, 'user_meta', OLD.id, 'UPDATE');




SET sql_mode = '';
CREATE TABLE IF NOT EXISTS project.user_events (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id      BIGINT(20)   NOT NULL,
    parent_type  VARCHAR(20)  NOT NULL,
    parent_id    BIGINT(20)   NOT NULL,
    event_name   VARCHAR(20)  NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
    FOREIGN KEY (user_id) REFERENCES project.users (id) ON DELETE CASCADE,
            KEY parent_type (parent_type),
            KEY parent_id   (parent_id),
            KEY event_name  (event_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




INSERT INTO project.users (user_status, user_token, user_email, user_name, user_hash) VALUES ('pending', 'token', 'noreply@noreply.no', 'art abramov', '');
UPDATE project.users SET user_status='approved' WHERE id=1;
SELECT * FROM project.users;
INSERT INTO project.user_meta (user_id, meta_key, meta_value) VALUES (1, 'key', 'value');
UPDATE project.users SET user_status='approved' WHERE id=1;
UPDATE project.user_meta SET meta_value='value 2' WHERE id=7;

SELECT * FROM project.users; SELECT * FROM project.user_meta; SELECT * FROM project.user_events;


# ============================================

# users +
CREATE TABLE IF NOT EXISTS project.users (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL,
    update_date DATETIME     NOT NULL,
    user_status VARCHAR(20)  NOT NULL, # pending | approved | trash
    user_token  VARCHAR(80)  NOT NULL,
    user_email  VARCHAR(255) NOT NULL,
    user_name   VARCHAR(128) NOT NULL,
    user_hash   VARCHAR(40)  NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY user_status (user_status),
    UNIQUE  KEY user_token  (user_token),
    UNIQUE  KEY user_email  (user_email),
            KEY user_name   (user_name),
            KEY user_hash   (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# hubs +
CREATE TABLE IF NOT EXISTS project.hubs (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL,
    update_date DATETIME     NOT NULL,
    user_id     BIGINT(20)   NOT NULL,
    hub_status  VARCHAR(20)  NOT NULL, # private | custom | trash
    hub_name    VARCHAR(255) NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY user_id     (user_id),
            KEY hub_status  (hub_status),
            KEY hub_name    (hub_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# roles +
CREATE TABLE IF NOT EXISTS project.roles (
    id          BIGINT(20)  NOT NULL AUTO_INCREMENT,
    create_date DATETIME    NOT NULL,
    update_date DATETIME    NOT NULL,
    hub_id      BIGINT(20)  NOT NULL,
    user_id     BIGINT(20)  NOT NULL,
    user_role   VARCHAR(20) NOT NULL, # admin | editor | commenter | reader | invited

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY hub_id      (hub_id),
            KEY user_id     (user_id),
            KEY user_role   (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# posts +
CREATE TABLE IF NOT EXISTS project.posts (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    create_date  DATETIME    NOT NULL,
    update_date  DATETIME    NOT NULL,
    parent_id    BIGINT(20)  NOT NULL,
    user_id      BIGINT(20)  NOT NULL,
    hub_id       BIGINT(20)  NOT NULL,
    post_type    VARCHAR(20) NOT NULL, # document | comment
    post_status  VARCHAR(20) NOT NULL, # draft | todo | doing | done | inherit | trash
    post_content TEXT        NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY parent_id   (parent_id),
            KEY user_id     (user_id),
            KEY hub_id      (hub_id),
            KEY post_type   (post_type),
            KEY post_status (post_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# tags +
CREATE TABLE IF NOT EXISTS project.tags (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL,
    update_date DATETIME     NOT NULL,
    post_id     BIGINT(20)   NOT NULL,
    tag_key     VARCHAR(20)  NOT NULL,
    tag_value   VARCHAR(255) NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY post_id     (post_id),
            KEY tag_key     (tag_key),
            KEY tag_value   (tag_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
CREATE TABLE IF NOT EXISTS project.uploads (
    id            BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date   DATETIME     NOT NULL,
    update_date   DATETIME     NOT NULL,
    post_id       BIGINT(20)   NOT NULL,
    upload_name   VARCHAR(255) NOT NULL,
    upload_mime   VARCHAR(255) NOT NULL,
    upload_size   BIGINT(20)   NOT NULL,
    upload_file   VARCHAR(255) NOT NULL,

    PRIMARY KEY id            (id),
            KEY create_date   (create_date),
            KEY update_date   (update_date),
            KEY post_id       (post_id),
            KEY upload_name   (upload_name),
            KEY upload_mime   (upload_mime),
            KEY upload_size   (upload_size),
     UNIQUE KEY upload_file   (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# meta
CREATE TABLE IF NOT EXISTS project.meta (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL,
    update_date DATETIME     NOT NULL,
    parent_type VARCHAR(20)  NOT NULL,
    parent_id   BIGINT(20)   NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL,

    PRIMARY KEY id          (id),
            KEY create_date (create_date),
            KEY update_date (update_date),
            KEY parent_type (parent_type),
            KEY parent_id   (parent_id),
            KEY meta_key    (meta_key),
            KEY meta_value  (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
