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
    hub_status  ENUM('custom', 'trash') NOT NULL,
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
CREATE TABLE IF NOT EXISTS hub_meta (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    hub_id      BIGINT(20)   UNSIGNED NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
            KEY (meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;






SET sql_mode = '';
CREATE TABLE IF NOT EXISTS posts (
    id            BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id       BIGINT(20)  UNSIGNED NOT NULL,
    hub_id        BIGINT(20)  UNSIGNED NOT NULL,
    post_status   ENUM('todo', 'doing', 'done', 'trash') NOT NULL,
    post_title    VARCHAR(255) NOT NULL,

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
CREATE TABLE IF NOT EXISTS post_tags (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    post_id     BIGINT(20)   UNSIGNED NOT NULL,
    tag_value   VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
            KEY (tag_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS post_comments (
    id           BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id      BIGINT(20)  UNSIGNED NOT NULL,
    post_id      BIGINT(20)  UNSIGNED NOT NULL,
    comment_text TEXT        NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS post_uploads (
    id            BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id       BIGINT(20)   UNSIGNED NOT NULL,
    comment_id    BIGINT(20)   UNSIGNED NULL,
    upload_status ENUM('uploaded', 'favorite', 'trash') NOT NULL,
    upload_name   VARCHAR(255) NOT NULL,
    upload_mime   VARCHAR(255) NOT NULL,
    upload_size   BIGINT(20)   NOT NULL,
    upload_file   VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (comment_id) REFERENCES post_comments (id) ON DELETE SET NULL,
            KEY (upload_status),
            KEY (upload_name),
            KEY (upload_mime),
            KEY (upload_size),
     UNIQUE KEY (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER |
CREATE TRIGGER post_insert
AFTER INSERT
ON posts 
FOR EACH ROW 
BEGIN
    SET @post_count := (SELECT COUNT(id) FROM posts WHERE hub_id = NEW.hub_id);
    IF EXISTS (SELECT id FROM hub_meta WHERE hub_id=NEW.hub_id AND meta_key='post_count') THEN
        UPDATE hub_meta SET meta_value=@post_count WHERE hub_id=NEW.hub_id AND meta_key='post_count';
    ELSE
        INSERT INTO hub_meta (hub_id, meta_key, meta_value) VALUES (NEW.hub_id, 'post_count', @post_count);
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER comment_insert
AFTER INSERT
ON post_comments 
FOR EACH ROW 
BEGIN
    SET @comment_count := (SELECT COUNT(id) FROM post_comments WHERE post_id = NEW.post_id);
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
ON post_comments 
FOR EACH ROW 
BEGIN
    SET @comment_count := (SELECT COUNT(id) FROM post_comments WHERE post_id = OLD.post_id);
    IF @comment_count = 0 THEN
        DELETE FROM post_meta WHERE post_id=OLD.post_id AND meta_key='comment_count';
    ELSE 
        UPDATE post_meta SET meta_value=@comment_count WHERE post_id=OLD.post_id AND meta_key='comment_count';
    END IF;
END;
|
DELIMITER ;


# ==========================================================================================================


DROP TABLE IF EXISTS post_uploads;
DROP TABLE IF EXISTS post_comments;
DROP TABLE IF EXISTS post_tags;
DROP TABLE IF EXISTS post_meta;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS user_meta;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS hub_meta;
DROP TABLE IF EXISTS hubs;
DROP TABLE IF EXISTS users;

DELETE FROM post_uploads;
DELETE FROM post_comments;
DELETE FROM post_tags;
DELETE FROM post_meta;
DELETE FROM posts;
DELETE FROM user_meta;
DELETE FROM user_roles;
DELETE FROM hub_meta;
DELETE FROM hubs;
DELETE FROM users;

SELECT * FROM users; SELECT * FROM hubs; SELECT * FROM hub_meta; SELECT * FROM user_roles; SELECT * FROM user_meta; SELECT * FROM posts; SELECT * FROM post_meta; SELECT * FROM post_tags; SELECT * FROM post_comments;
