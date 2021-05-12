SET sql_mode = '';
CREATE TABLE IF NOT EXISTS users (
    id           BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    restore_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_status  ENUM('pending', 'approved', 'trash') NOT NULL,
    user_token   VARCHAR(80)  NOT NULL,
    user_email   VARCHAR(255) NOT NULL,
    user_name    VARCHAR(128) NOT NULL,
    user_hash    VARCHAR(40)  NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (user_status),
     UNIQUE KEY (user_token),
     UNIQUE KEY (user_email),
            KEY (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS user_meta (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
     UNIQUE KEY (user_id, meta_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS repos (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    repo_status  ENUM('private', 'custom', 'trash') NOT NULL,
    repo_name   VARCHAR(128) NOT NULL,

    PRIMARY KEY id          (id),
    FOREIGN KEY user_id     (user_id) REFERENCES users (id) ON DELETE CASCADE,
            KEY repo_status (repo_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS user_roles (
    id          BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  UNSIGNED NOT NULL,
    repo_id     BIGINT(20)  UNSIGNED NOT NULL,
    user_role  ENUM('admin', 'author', 'editor', 'reader', 'none') NOT NULL,

        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (repo_id) REFERENCES repos (id) ON DELETE CASCADE,
         UNIQUE KEY (user_id, repo_id),
                KEY (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS posts (
    id           BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    parent_id    BIGINT(20)  UNSIGNED NULL DEFAULT NULL,
    user_id      BIGINT(20)  UNSIGNED NOT NULL,
    repo_id      BIGINT(20)  UNSIGNED NOT NULL,
    post_status  ENUM('todo', 'doing', 'done', 'comment', 'trash') NOT NULL,
    post_content TEXT        NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES posts (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (repo_id) REFERENCES repos (id) ON DELETE CASCADE,
            KEY (post_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS post_meta (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    post_id     BIGINT(20)   UNSIGNED NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
            KEY (meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS post_uploads (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    post_id     BIGINT(20)   UNSIGNED NULL DEFAULT NULL,
    upload_name VARCHAR(255) NOT NULL,
    upload_mime VARCHAR(255) NOT NULL,
    upload_size BIGINT(20)   NOT NULL,
    upload_file VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE SET NULL,
            KEY (upload_name),
            KEY (upload_mime),
            KEY (upload_size),
     UNIQUE KEY (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# ==========================================================================================================

DROP TABLE IF EXISTS user_meta;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS post_uploads;
DROP TABLE IF EXISTS post_meta;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS repos;
DROP TABLE IF EXISTS users;


CREATE TRIGGER post_delete
AFTER DELETE
ON posts 
FOR EACH ROW
UPDATE post_uploads SET post_id=NULL WHERE post_id=OLD.id;

# ==========================================================================================================

SELECT * FROM users; SELECT * FROM repos; SELECT * FROM user_roles; SELECT * FROM posts; SELECT * FROM post_uploads;

INSERT INTO posts (user_id, repo_id, post_status, post_content) VALUES (1, 1, 'todo', 'parent content 1');
INSERT INTO posts (user_id, repo_id, post_status, post_content) VALUES (1, 1, 'todo', 'parent content 2');
INSERT INTO posts (parent_id, user_id, repo_id, post_content) VALUES (1, 1, 1, 'child content 3');

INSERT INTO post_uploads (post_id, upload_name, upload_mime, upload_size, upload_file) VALUES (2, 'upload name', 'file/mime', 100, 'file-1');
INSERT INTO post_uploads (post_id, upload_name, upload_mime, upload_size, upload_file) VALUES (3, 'upload name', 'file/mime', 100, 'file-2');

DELETE FROM posts WHERE id=1;







insert into posts (user_id, repo_id, post_type, post_status, post_content) VALUES (1, 1, 'document', 'todo', 'parent');
insert into posts (parent_id, user_id, repo_id, post_type, post_status, post_content) VALUES (1, 1, 1, 'document', 'todo', 'child');

INSERT INTO users (user_status, user_token, user_email, user_name, user_hash) VALUES ('pending', 'token', 'noreply@noreply.no', 'art abramov', '');
UPDATE users SET user_status='approved' WHERE id=1;
SELECT * FROM users;
INSERT INTO user_meta (user_id, meta_key, meta_value) VALUES (1, 'key', 'value');
UPDATE users SET user_status='approved' WHERE id=1;
UPDATE user_meta SET meta_value='value 2' WHERE id=7;

SELECT * FROM users; SELECT * FROM user_meta; SELECT * FROM user_events;


# ============================================


CREATE TRIGGER user_meta_insert
AFTER INSERT 
ON user_meta 
FOR EACH ROW
INSERT INTO user_events(user_id, parent_type, parent_id, event_name) VALUES(NEW.user_id, 'user_meta', NEW.id, 'INSERT');

CREATE TRIGGER user_meta_update
AFTER UPDATE 
ON user_meta 
FOR EACH ROW
INSERT INTO user_events(user_id, parent_type, parent_id, event_name) VALUES(OLD.user_id, 'user_meta', OLD.id, 'UPDATE');


CREATE TRIGGER user_restore
AFTER UPDATE 
ON users
FOR EACH ROW
BEGIN
    IF NEW.user_hash <> OLD.user_hash THEN
        SET OLD.restore_date = NOW();
    END IF;
END;



CREATE TRIGGER user_restore 
BEFORE UPDATE ON users
FOR EACH ROW 
BEGIN
  IF NEW.user_hash <> OLD.user_hash THEN
    SET NEW.restore_date = NOW();
  END IF;
END;




CREATE TRIGGER user_restore 
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
SET NEW.restore_date = NOW();
END;



UPDATE users SET NEW.restore_date = NOW();
UPDATE users SET NEW.restore_date=NOW() WHERE id=7;