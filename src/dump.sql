SET sql_mode = '';
CREATE TABLE IF NOT EXISTS users (
    id           BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    restore_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_status  ENUM('pending', 'approved', 'trash') NOT NULL,
    user_token   CHAR(80)  NOT NULL,
    user_email   VARCHAR(255) NOT NULL,
    user_name    VARCHAR(128) NOT NULL,
    user_hash    VARCHAR(40)  NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
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
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            KEY (meta_key),
     UNIQUE KEY (user_id, meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS hubs (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    hub_status  ENUM('private', 'custom', 'public', 'trash') NOT NULL,
    hub_name    VARCHAR(128) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            KEY (hub_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS user_roles (
    id          BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  UNSIGNED NOT NULL,
    hub_id      BIGINT(20)  UNSIGNED NOT NULL,
    user_role  ENUM('admin', 'author', 'editor', 'reader', 'none') NOT NULL,

        PRIMARY KEY (id),
                KEY (create_date),
                KEY (update_date),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
         UNIQUE KEY (user_id, hub_id),
                KEY (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS posts (
    id          BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  UNSIGNED NOT NULL,
    hub_id      BIGINT(20)  UNSIGNED NOT NULL,
    post_status ENUM('todo', 'doing', 'done', 'trash') NOT NULL,
    post_title  VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
            KEY (post_status),
            KEY (post_title)
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
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
            KEY (meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS comments (
    id           BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id      BIGINT(20)  UNSIGNED NOT NULL,
    post_id      BIGINT(20)  UNSIGNED NOT NULL,
    comment_text TEXT        NULL DEFAULT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS comment_uploads (
    id            BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id       BIGINT(20)   UNSIGNED NOT NULL,
    comment_id    BIGINT(20)   UNSIGNED NULL DEFAULT NULL,
    upload_status ENUM('uploaded', 'favorite', 'trash') NOT NULL,
    upload_name   VARCHAR(255) NOT NULL,
    upload_mime   VARCHAR(255) NOT NULL,
    upload_size   BIGINT(20)   NOT NULL,
    upload_file   VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE SET NULL,
            KEY (upload_status),
            KEY (upload_name),
            KEY (upload_mime),
            KEY (upload_size),
     UNIQUE KEY (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER |
CREATE TRIGGER comment_insert
AFTER INSERT
ON comments 
FOR EACH ROW 
BEGIN
    SET @comment_count := (SELECT COUNT(id) FROM comments WHERE post_id = NEW.post_id);
    IF EXISTS (SELECT id FROM post_meta WHERE post_id=NEW.post_id AND meta_key='comment_count') THEN
        UPDATE post_meta SET meta_value=@comment_count WHERE post_id=NEW.post_id AND meta_key='comment_count';
    ELSE
        INSERT INTO post_meta (post_id, meta_key, meta_value) VALUES (NEW.post_id, 'comment_count', @comment_count);
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER comment_delete
AFTER DELETE
ON comments 
FOR EACH ROW 
BEGIN
    SET @comment_count := (SELECT COUNT(id) FROM comments WHERE post_id = OLD.post_id);
    IF @comment_count = 0 THEN
        DELETE FROM post_meta WHERE post_id=OLD.post_id AND meta_key='comment_count';
    ELSE 
        UPDATE post_meta SET meta_value=@comment_count WHERE post_id=OLD.post_id AND meta_key='comment_count';
    END IF;
END;
|
DELIMITER ;


# ==========================================================================================================


DROP TABLE IF EXISTS comment_uploads;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS post_meta;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS user_meta;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS hubs;
DROP TABLE IF EXISTS users;

DELETE FROM comment_uploads;
DELETE FROM comments;
DELETE FROM post_meta;
DELETE FROM posts;
DELETE FROM user_meta;
DELETE FROM user_roles;
DELETE FROM hubs;
DELETE FROM users;

SELECT * FROM users; SELECT * FROM hubs; SELECT * FROM user_roles; SELECT * FROM user_meta; SELECT * FROM posts;






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


DELIMITER |
CREATE TRIGGER post_insert
AFTER INSERT
ON posts 
FOR EACH ROW 
BEGIN
    IF NEW.parent_id IS NOT NULL THEN
        SET @childs_count := (SELECT COUNT(id) FROM posts WHERE parent_id = NEW.parent_id);
        IF EXISTS (SELECT id FROM post_meta WHERE post_id=NEW.parent_id AND meta_key='childs_count') THEN
            UPDATE post_meta SET meta_value=@childs_count WHERE post_id=NEW.parent_id AND meta_key='childs_count';
        ELSE
            INSERT INTO post_meta (post_id, meta_key, meta_value) VALUES (NEW.parent_id, 'childs_count', @childs_count);
        END IF;
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER post_delete
AFTER DELETE
ON posts 
FOR EACH ROW 
BEGIN
    IF OLD.parent_id IS NOT NULL THEN
        SET @childs_count := (SELECT COUNT(id) FROM posts WHERE parent_id = OLD.parent_id);
        IF @childs_count = 0 THEN
            #DELETE FROM post_meta WHERE post_id=OLD.parent_id AND meta_key='childs_count';
        #ELSE 
            UPDATE post_meta SET meta_value=@childs_count WHERE post_id=OLD.parent_id AND meta_key='childs_count';
        END IF;
    END IF;
END;
|
DELIMITER ;



SET sql_mode = '';
CREATE TABLE IF NOT EXISTS post_uploads (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    repo_id     BIGINT(20)   UNSIGNED NULL DEFAULT NULL,
    post_id     BIGINT(20)   UNSIGNED NULL DEFAULT NULL,
    upload_key  VARCHAR(20)  NOT NULL,
    upload_name VARCHAR(255) NOT NULL,
    upload_mime VARCHAR(255) NOT NULL,
    upload_size BIGINT(20)   NOT NULL,
    upload_file VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (repo_id) REFERENCES repos (id) ON DELETE SET NULL,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE SET NULL,
            KEY (upload_key),
            KEY (upload_name),
            KEY (upload_mime),
            KEY (upload_size),
     UNIQUE KEY (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# ==========================================================================================================






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