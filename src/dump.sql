
SET sql_mode = '';
CREATE TABLE IF NOT EXISTS users (
    id           BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    restore_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_status  ENUM('pending', 'approved', 'premium', 'trash') NOT NULL,
    user_token   CHAR(80)     NOT NULL,
    user_email   VARCHAR(255) NOT NULL,
    user_name    VARCHAR(255) NOT NULL,
    user_hash    VARCHAR(40)  NOT NULL DEFAULT '',

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
    user_role  ENUM('admin', 'author', 'editor', 'reader', 'none') NOT NULL,

        PRIMARY KEY (id),
                KEY (create_date),
                KEY (update_date),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
        FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
         UNIQUE KEY (user_id, hub_id),
                KEY (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS posts (
    id            BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id       BIGINT(20)  UNSIGNED NOT NULL,
    hub_id        BIGINT(20)  UNSIGNED NOT NULL,
    post_type     ENUM('document') NOT NULL,
    post_status   ENUM('todo', 'doing', 'done', 'trash') NOT NULL,
    post_excerpt  VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
            KEY (post_type),
            KEY (post_status),
            KEY (post_excerpt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS comments (
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
CREATE TABLE IF NOT EXISTS uploads (
    id            BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date   DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id       BIGINT(20)   UNSIGNED NOT NULL,
    comment_id    BIGINT(20)   UNSIGNED NULL,
    upload_name   VARCHAR(255) NOT NULL,
    upload_mime   VARCHAR(255) NOT NULL,
    upload_size   BIGINT(20)   NOT NULL,
    upload_file   VARCHAR(255) NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE SET NULL,
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
    parent_type ENUM('users', 'hubs', 'roles', 'posts', 'comments', 'uploads') NOT NULL,
    parent_id   BIGINT(20)   UNSIGNED NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
            KEY (parent_type),
            KEY (parent_id),
            KEY (meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER |
CREATE TRIGGER post_insert
AFTER INSERT
ON posts 
FOR EACH ROW 
BEGIN
    IF NEW.post_type = 'document' THEN
        SET @document_count := (SELECT COUNT(id) FROM posts WHERE hub_id = NEW.hub_id AND post_type = 'document');
        IF EXISTS (SELECT id FROM meta WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='document_count') THEN
            UPDATE meta SET meta_value=@document_count WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='document_count';
        ELSE
            INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('hubs', NEW.hub_id, 'document_count', @document_count);
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
    DELETE FROM meta WHERE parent_type = 'posts' AND parent_id=OLD.id;

    IF OLD.post_type = 'document' THEN
        SET @document_count := (SELECT COUNT(id) FROM posts WHERE hub_id = OLD.hub_id AND post_type = 'document');
        IF @document_count = 0 THEN
            DELETE FROM meta WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key = 'document_count';
        ELSE
            UPDATE meta SET meta_value=@document_count WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key='document_count';
        END IF;
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER comment_insert
AFTER INSERT
ON comments 
FOR EACH ROW 
BEGIN
    SET @comment_count := (SELECT COUNT(id) FROM comments WHERE post_id = NEW.post_id);
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'posts' AND parent_id = NEW.post_id AND meta_key='comment_count') THEN
        UPDATE meta SET meta_value=@comment_count WHERE parent_type = 'posts' AND parent_id = NEW.post_id AND meta_key='comment_count';
    ELSE
        INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('posts', NEW.post_id, 'comment_count', @comment_count);
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
        DELETE FROM meta WHERE parent_type = 'posts' AND parent_id = OLD.post_id AND meta_key = 'comment_count';
    ELSE
        UPDATE meta SET meta_value=@comment_count WHERE parent_type = 'posts' AND parent_id = OLD.post_id AND meta_key='comment_count';
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER upload_insert
AFTER INSERT
ON uploads 
FOR EACH ROW 
BEGIN
    SET @upload_sum := (SELECT SUM(upload_size) FROM uploads WHERE user_id = NEW.user_id);
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'users' AND parent_id = NEW.user_id AND meta_key='upload_sum') THEN
        UPDATE meta SET meta_value=@upload_sum WHERE parent_type = 'users' AND parent_id = NEW.user_id AND meta_key='upload_sum';
    ELSE
        INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('users', NEW.user_id, 'upload_sum', @upload_sum);
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER upload_delete
AFTER DELETE
ON uploads 
FOR EACH ROW 
BEGIN
    SET @upload_sum := (SELECT SUM(upload_size) FROM uploads WHERE user_id = OLD.user_id);
    IF @upload_sum = 0 THEN
        DELETE FROM meta WHERE parent_type = 'users' AND parent_id = OLD.user_id AND meta_key = 'upload_sum';
    ELSE
        UPDATE meta SET meta_value=@upload_sum WHERE parent_type = 'users' AND parent_id = OLD.user_id AND meta_key='upload_sum';
    END IF;
END;
| 
DELIMITER ;


DROP TABLE IF EXISTS meta;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS hubs;
DROP TABLE IF EXISTS users;

DELETE FROM meta;
DELETE FROM uploads;
DELETE FROM comments;
DELETE FROM posts;
DELETE FROM roles;
DELETE FROM hubs;
DELETE FROM users;


SELECT * FROM users; SELECT * FROM hubs; SELECT * FROM roles; SELECT * FROM posts; SELECT * FROM comments; SELECT * FROM uploads; SELECT * FROM meta; 


