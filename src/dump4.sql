
SET sql_mode = '';
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    restore_date  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_status   ENUM('pending', 'approved', 'trash') NOT NULL,
    user_token    CHAR(80)     NOT NULL,
    user_email    VARCHAR(255) NOT NULL,
    user_name     VARCHAR(255) NOT NULL,
    user_hash     VARCHAR(40)  NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
            KEY (restore_date),
            KEY (user_status),
     UNIQUE KEY (user_token),
     UNIQUE KEY (user_email),
            KEY (user_name),
            KEY (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS hubs (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    hub_status  ENUM('custom', 'trash') NOT NULL,
    hub_name    VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (hub_status),
            KEY (hub_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS roles (
    id          BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  UNSIGNED NOT NULL,
    hub_id      BIGINT(20)  UNSIGNED NOT NULL,
    role_name   ENUM('admin', 'author', 'editor', 'reader') NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
     UNIQUE KEY (user_id, hub_id),
            KEY (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS posts (
    id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date    DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date    DATETIME   NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id        BIGINT(20) UNSIGNED NOT NULL,
    hub_id         BIGINT(20) UNSIGNED NOT NULL,
    post_type      ENUM('doc') NOT NULL,
    post_status    ENUM('todo', 'doing', 'done', 'trash') NOT NULL,
    post_title     VARCHAR(255) NOT NULL,
    post_text      TEXT NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
            KEY (post_type),
            KEY (post_status),
            KEY (post_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS comments (
    id           BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id      BIGINT(20)  UNSIGNED NOT NULL,
    parent_type  ENUM('post') NOT NULL,
    parent_id    BIGINT(20) UNSIGNED NOT NULL,
    comment_text TEXT        NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (parent_type),
            KEY (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS uploads (
    id            BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id       BIGINT(20)   UNSIGNED NOT NULL,
    parent_type  ENUM('post', 'comment') NOT NULL,
    parent_id    BIGINT(20) UNSIGNED NOT NULL,
    upload_name   VARCHAR(255) NOT NULL,
    upload_mime   VARCHAR(255) NOT NULL,
    upload_size   BIGINT(20)   NOT NULL,
    upload_file   VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (parent_type),
            KEY (parent_id),
            KEY (upload_name),
            KEY (upload_mime),
            KEY (upload_size),
     UNIQUE KEY (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS meta (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
    parent_type ENUM('user', 'hub', 'post') NOT NULL,
    parent_id   BIGINT(20)   UNSIGNED NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (parent_type),
            KEY (parent_id),
            KEY (meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS amounts (
    id           BIGINT(20)    UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id      BIGINT(20)    UNSIGNED NOT NULL DEFAULT 0,
    parent_type  ENUM('post', 'comment') NOT NULL,
    parent_id    BIGINT(20)    UNSIGNED NOT NULL,
    amount_key   VARCHAR(20)   NOT NULL,
    amount_value DECIMAL(22,2) NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (parent_type),
            KEY (parent_id),
            KEY (amount_key),
            KEY (amount_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS timers (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL DEFAULT 0,
    parent_type ENUM('post', 'comment') NOT NULL,
    parent_id   BIGINT(20)   UNSIGNED NOT NULL,
    timer_key   VARCHAR(20)  NOT NULL,
    timer_value VARCHAR(255) NOT NULL DEFAULT '0000-00-00 00:00:00',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (parent_type),
            KEY (parent_id),
            KEY (timer_key),
            KEY (timer_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;







DELIMITER |
CREATE TRIGGER role_insert
AFTER INSERT
ON user_roles 
FOR EACH ROW 
BEGIN
    SET @roles_count := (SELECT COUNT(id) FROM user_roles WHERE hub_id = NEW.hub_id AND user_role <> 'invited');
    UPDATE hubs SET roles_count=@roles_count WHERE id = NEW.hub_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER role_update
AFTER INSERT
ON user_roles 
FOR EACH ROW 
BEGIN
    SET @roles_count := (SELECT COUNT(id) FROM user_roles WHERE hub_id = NEW.hub_id AND user_role <> 'invited');
    UPDATE hubs SET roles_count=@roles_count WHERE id = NEW.hub_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER role_delete
AFTER DELETE
ON user_roles 
FOR EACH ROW 
BEGIN
    SET @roles_count := (SELECT COUNT(id) FROM user_roles WHERE hub_id = OLD.hub_id AND user_role <> 'invited');
    UPDATE hubs SET roles_count=@roles_count WHERE id = OLD.hub_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER post_insert
AFTER INSERT
ON posts
FOR EACH ROW 
BEGIN
    SET @posts_count := (SELECT COUNT(id) FROM posts WHERE hub_id = NEW.hub_id);
    UPDATE hubs SET posts_count=@posts_count WHERE id = NEW.hub_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER post_update
AFTER INSERT
ON posts
FOR EACH ROW 
BEGIN
    SET @posts_count := (SELECT COUNT(id) FROM posts WHERE hub_id = NEW.hub_id);
    UPDATE hubs SET posts_count=@posts_count WHERE id = NEW.hub_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER post_delete
AFTER DELETE
ON posts 
FOR EACH ROW 
BEGIN
    SET @posts_count := (SELECT COUNT(id) FROM posts WHERE hub_id = OLD.hub_id);
    UPDATE hubs SET posts_count=@posts_count WHERE id = OLD.hub_id;
END;
| 
DELIMITER ;

DELIMITER |
CREATE TRIGGER upload_insert
AFTER INSERT
ON comment_uploads 
FOR EACH ROW 
BEGIN
    SET @uploads_count := (SELECT COUNT(id) FROM comment_uploads WHERE user_id = NEW.user_id);
    SET @uploads_sum := (SELECT SUM(upload_size) FROM comment_uploads WHERE user_id = NEW.user_id);
    UPDATE users SET uploads_count=@uploads_count WHERE id=NEW.user_id;
    UPDATE users SET uploads_sum=@uploads_sum WHERE id=NEW.user_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER upload_delete
AFTER DELETE
ON comment_uploads 
FOR EACH ROW 
BEGIN
    SET @uploads_count := (SELECT COUNT(id) FROM comment_uploads WHERE user_id = OLD.user_id);
    SET @uploads_sum := (SELECT SUM(upload_size) FROM comment_uploads WHERE user_id = OLD.user_id);
    UPDATE users SET uploads_count=@uploads_count WHERE id=OLD.user_id;
    UPDATE users SET uploads_sum=@uploads_sum WHERE id=OLD.user_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER comment_insert
AFTER INSERT
ON post_comments 
FOR EACH ROW 
BEGIN
    SET @comments_count := (SELECT COUNT(id) FROM post_comments WHERE post_id = NEW.post_id);
    UPDATE posts SET comments_count=@comments_count WHERE id = NEW.post_id;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER comment_delete
AFTER DELETE
ON post_comments 
FOR EACH ROW 
BEGIN
    SET @comments_count := (SELECT COUNT(id) FROM post_comments WHERE post_id = OLD.post_id);
    UPDATE posts SET comments_count=@comments_count WHERE id = OLD.post_id;
END;
| 
DELIMITER ;


DROP TABLE IF EXISTS meta;
DROP TABLE IF EXISTS comment_uploads;
DROP TABLE IF EXISTS post_comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS hubs;
DROP TABLE IF EXISTS users;

DELETE FROM meta;
DELETE FROM comment_uploads;
DELETE FROM post_comments;
DELETE FROM posts;
DELETE FROM user_roles;
DELETE FROM hubs;
DELETE FROM users;


SELECT * FROM users; SELECT * FROM hubs; SELECT * FROM user_roles; SELECT * FROM posts; SELECT * FROM post_comments; SELECT * FROM comment_uploads; SELECT * FROM meta; 


INSERT INTO users (user_status, user_token, user_email, user_name) VALUES ('pending', 'token1token1token1token1token1token1token1token1token1token1token1token1token1to', '14november@mail.ru', 'art abramov');
INSERT INTO users (user_status, user_token, user_email, user_name) VALUES ('pending', 'token2token2token2token2token2token2token2token2token2token2token2token2token2to', '15november@mail.ru', 'no name');
INSERT INTO hubs (user_id, hub_status, hub_name) VALUES (1, 'custom', 'private hub');
INSERT INTO user_roles (user_id, hub_id, user_role) VALUES (1, 1, 'admin');
INSERT INTO user_roles (user_id, hub_id, user_role) VALUES (2, 1, 'admin');
INSERT INTO posts (user_id, hub_id, post_status, post_excerpt) VALUES (1, 1, 'todo', 'lorem ipsum');
INSERT INTO posts (user_id, hub_id, post_status, post_excerpt) VALUES (1, 1, 'todo', 'lorem ipsum');
INSERT INTO post_comments (user_id, post_id, comment_text) VALUES (1, 1, 'lorem ipsum');
INSERT INTO post_comments (user_id, post_id, comment_text) VALUES (1, 1, 'lorem ipsum');
INSERT INTO post_comments (user_id, post_id, comment_text) VALUES (1, 2, 'lorem ipsum');
INSERT INTO comment_uploads (user_id, comment_id, upload_name, upload_mime, upload_size, upload_file) VALUES (1, 1, 'upload_name', 'upload_mime', 100, 'upload_file');
INSERT INTO comment_uploads (user_id, comment_id, upload_name, upload_mime, upload_size, upload_file) VALUES (1, 1, 'upload_name', 'upload_mime', 100, 'upload_file2');
INSERT INTO meta (user_id, parent_type, parent_id, meta_key, meta_value) VALUES (1, 'users', 1, 'user_tag', 'user 1');
INSERT INTO meta (user_id, parent_type, parent_id, meta_key, meta_value) VALUES (2, 'users', 2, 'user_tag', 'user 2');



INSERT INTO users (user_status, user_token, user_email, user_name) VALUES ('pending', 'token1token1token1token1token1token1token1token1token1token1token1token1token1to', 'email1@email.e', 'name1');
INSERT INTO users (user_status, user_token, user_email, user_name) VALUES ('pending', 'token2token2token2token2token2token2token2token2token2token2token2token2token2to', 'email2@email.e', 'name2');
INSERT INTO meta (user_id, parent_type, parent_id, meta_key, meta_value) VALUES (1, 'users', 1, 'posts_count', '11');
INSERT INTO meta (user_id, parent_type, parent_id, meta_key, meta_value) VALUES (1, 'users', 1, 'comments_count', '22');
SELECT users.*, meta.meta_value AS posts_count FROM users LEFT JOIN meta ON meta.user_id=users.id WHERE users.id=1 AND meta.parent_type='users' AND meta.meta_key='posts_count';


DELIMITER |
CREATE TRIGGER role_insert
AFTER INSERT
ON user_roles 
FOR EACH ROW 
BEGIN
    SET @roles_count := (SELECT COUNT(id) FROM roles WHERE hub_id = OLD.hub_id AND user_role NOT IN ('invited', 'banned'));
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='roles_count') THEN
        UPDATE meta SET meta_value=@roles_count WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='roles_count';
    ELSE
        INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('hubs', NEW.hub_id, 'roles_count', @roles_count);
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER role_delete
AFTER DELETE
ON roles 
FOR EACH ROW 
BEGIN
    SET @roles_count := (SELECT COUNT(id) FROM roles WHERE hub_id = OLD.hub_id AND user_role NOT IN ('invited', 'banned'));
    IF @roles_count = 0 THEN
        DELETE FROM meta WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key = 'roles_count';
    ELSE
        UPDATE meta SET meta_value=@roles_count WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key='roles_count';
    END IF;
END;
| 
DELIMITER ;









