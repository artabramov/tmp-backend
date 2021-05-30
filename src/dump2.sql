
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


    #posts_count    BIGINT(20)   UNSIGNED NOT NULL,
    #comments_count BIGINT(20)   UNSIGNED NOT NULL,
    #uploads_count  BIGINT(20)   UNSIGNED NOT NULL,
    #uploads_sum    BIGINT(20)   UNSIGNED NOT NULL,

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

INSERT INTO users (user_status, user_token, user_email, user_name) VALUES ('pending', 'token1token1token1token1token1token1token1token1token1token1token1token1token1to', 'email1@email.e', 'name1');
INSERT INTO users (user_status, user_token, user_email, user_name) VALUES ('pending', 'token2token2token2token2token2token2token2token2token2token2token2token2token2to', 'email2@email.e', 'name2');

SET sql_mode = '';
CREATE TABLE IF NOT EXISTS user_pals (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    pal_id      BIGINT(20)   UNSIGNED NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (pal_id) REFERENCES users (id) ON DELETE NO ACTION,
            UNIQUE KEY (user_id, pal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS hubs (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)   UNSIGNED NOT NULL,
    hub_status  ENUM('custom', 'trash') NOT NULL,
    hub_name    VARCHAR(255) NOT NULL,

    users_count    BIGINT(20)   UNSIGNED NOT NULL,
    posts_count    BIGINT(20)   UNSIGNED NOT NULL,
    comments_count BIGINT(20)   UNSIGNED NOT NULL,
    uploads_count  BIGINT(20)   UNSIGNED NOT NULL,
    uploads_sum    BIGINT(20)   UNSIGNED NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (hub_status),
            KEY (hub_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS user_roles (
    id          BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  UNSIGNED NOT NULL,
    hub_id      BIGINT(20)  UNSIGNED NOT NULL,
    user_role  ENUM('admin', 'author', 'editor', 'reader', 'invited') NOT NULL,

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
    id           BIGINT(20)  UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date  DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id      BIGINT(20)  UNSIGNED NOT NULL,
    hub_id       BIGINT(20)  UNSIGNED NOT NULL,
    post_status  ENUM('todo', 'doing', 'done', 'trash') NOT NULL,
    post_excerpt VARCHAR(255) NOT NULL,

    comments_count BIGINT(20)   UNSIGNED NOT NULL,
    uploads_count  BIGINT(20)   UNSIGNED NOT NULL,
    uploads_sum    BIGINT(20)   UNSIGNED NOT NULL,

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (hub_id) REFERENCES hubs (id) ON DELETE CASCADE,
            KEY (post_status),
            KEY (post_excerpt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET sql_mode = '';
CREATE TABLE IF NOT EXISTS post_tags (
    id          BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
    create_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  UNSIGNED NOT NULL,
    post_id     BIGINT(20)   UNSIGNED NOT NULL,
    post_tag    VARCHAR(128) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
            KEY (post_tag),
     UNIQUE KEY (post_id, post_tag)
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
CREATE TABLE IF NOT EXISTS comment_uploads (
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
    FOREIGN KEY (comment_id) REFERENCES post_comments (id) ON DELETE SET NULL,
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
    parent_type ENUM('users', 'hubs', 'posts') NOT NULL,
    parent_id   BIGINT(20)   UNSIGNED NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL, # post_notice
    meta_value  VARCHAR(255) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
            KEY (create_date),
            KEY (update_date),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION,
            KEY (parent_type),
            KEY (parent_id),
            KEY (meta_key),
     UNIQUE KEY (parent_type, parent_id, meta_key),
            KEY (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO meta (user_id, parent_type, parent_id, meta_key, meta_value) VALUES (1, 'users', 1, 'user_tag', 'tag1');
INSERT INTO meta (user_id, parent_type, parent_id, meta_key, meta_value) VALUES (1, 'users', 2, 'user_tag', 'tag2');


DELIMITER |
CREATE TRIGGER hub_insert
AFTER INSERT
ON hubs 
FOR EACH ROW 
BEGIN
    SET @user_hubs := (SELECT COUNT(id) FROM hubs WHERE user_id = NEW.user_id AND hub_status <> 'trash');
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'users' AND parent_id = NEW.user_id AND meta_key='user_hubs') THEN
        UPDATE meta SET meta_value=@user_hubs WHERE parent_type = 'users' AND parent_id = NEW.user_id AND meta_key='user_hubs';
    ELSE
        INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('users', NEW.user_id, 'user_hubs', @user_hubs);
    END IF;
END;
| 
DELIMITER ;









DELIMITER |
CREATE TRIGGER document_insert
AFTER INSERT
ON documents 
FOR EACH ROW 
BEGIN
    SET @hub_documents := (SELECT COUNT(id) FROM documents WHERE hub_id = NEW.hub_id);
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='hub_documents') THEN
        UPDATE meta SET meta_value=@hub_documents WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='hub_documents';
    ELSE
        INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('hubs', NEW.hub_id, 'hub_documents', @hub_documents);
    END IF;
END;
| 
DELIMITER ;


DELIMITER |
CREATE TRIGGER document_delete
AFTER DELETE
ON documents 
FOR EACH ROW 
BEGIN
    DELETE FROM meta WHERE parent_type = 'documents' AND parent_id=OLD.id;

    SET @document_count := (SELECT COUNT(id) FROM documents WHERE hub_id = OLD.hub_id);
    IF @document_count = 0 THEN
        DELETE FROM meta WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key = 'document_count';
    ELSE
        UPDATE meta SET meta_value=@document_count WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key='document_count';
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
    SET @comment_count := (SELECT COUNT(id) FROM document_comments WHERE document_id = NEW.document_id);
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'documents' AND parent_id = NEW.document_id AND meta_key='comment_count') THEN
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


DELIMITER |
CREATE TRIGGER role_insert
AFTER INSERT
ON roles 
FOR EACH ROW 
BEGIN
    SET @role_count := (SELECT COUNT(id) FROM roles WHERE hub_id = NEW.hub_id AND user_role <> 'none');
    IF EXISTS (SELECT id FROM meta WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='role_count') THEN
        UPDATE meta SET meta_value=@role_count WHERE parent_type = 'hubs' AND parent_id = NEW.hub_id AND meta_key='role_count';
    ELSE
        INSERT INTO meta (parent_type, parent_id, meta_key, meta_value) VALUES ('hubs', NEW.hub_id, 'role_count', @role_count);
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
    SET @role_count := (SELECT COUNT(id) FROM roles WHERE hub_id = OLD.hub_id AND user_role <> 'none');
    IF @role_count = 0 THEN
        DELETE FROM meta WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key = 'role_count';
    ELSE
        UPDATE meta SET meta_value=@role_count WHERE parent_type = 'hubs' AND parent_id = OLD.hub_id AND meta_key='role_count';
    END IF;
END;
| 
DELIMITER ;


DROP TABLE IF EXISTS meta;
DROP TABLE IF EXISTS comment_uploads;
DROP TABLE IF EXISTS document_comments;
DROP TABLE IF EXISTS document_tags;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS user_pals;
DROP TABLE IF EXISTS hubs;
DROP TABLE IF EXISTS users;

DELETE FROM meta;
DELETE FROM comment_uploads;
DELETE FROM document_comments;
DELETE FROM document_tags;
DELETE FROM documents;
DELETE FROM user_roles;
DELETE FROM user_pals;
DELETE FROM hubs;
DELETE FROM users;


SELECT * FROM users; SELECT * FROM hubs; SELECT * FROM user_roles; SELECT * FROM user_pals; SELECT * FROM documents; SELECT * FROM document_comments; SELECT * FROM document_tags; SELECT * FROM comment_uploads; SELECT * FROM meta; 

