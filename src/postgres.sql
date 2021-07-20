-- users --

CREATE SEQUENCE users_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE user_status AS ENUM ('pending', 'approved', 'trash');


CREATE TABLE IF NOT EXISTS users (
    id          BIGINT DEFAULT NEXTVAL('users_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    remind_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_status user_status,
    user_token  CHAR(80) NOT NULL UNIQUE,
    user_email  VARCHAR(255) NOT NULL UNIQUE,
    user_hash   VARCHAR(40) NOT NULL,
    user_name   VARCHAR(128) NOT NULL
);

-- users meta --

CREATE SEQUENCE users_meta_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS users_meta (
    id          BIGINT DEFAULT NEXTVAL('users_meta_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL,
    CONSTRAINT user_meta_uid UNIQUE(user_id, meta_key)
);

-- users vols --

CREATE SEQUENCE users_vols_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS users_vols (
    id          BIGINT DEFAULT NEXTVAL('users_vols_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    expire_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    vol_size    INT NOT NULL
);

-- hubs --

CREATE SEQUENCE hubs_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE hub_status AS ENUM ('custom', 'trash');

CREATE TABLE IF NOT EXISTS hubs (
    id          BIGINT DEFAULT NEXTVAL('hubs_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    hub_status  hub_status NOT NULL,
    hub_name    VARCHAR(128) NOT NULL
);

-- hubs meta --

CREATE SEQUENCE hubs_meta_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS hubs_meta (
    id          BIGINT DEFAULT NEXTVAL('hubs_meta_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    hub_id      BIGINT REFERENCES hubs(id) ON DELETE CASCADE NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL,
    CONSTRAINT hub_meta_uid UNIQUE(hub_id, meta_key)
);

-- users roles --

CREATE SEQUENCE users_roles_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE role_status AS ENUM ('admin', 'editor', 'reader');

CREATE TABLE IF NOT EXISTS users_roles (
    id          BIGINT DEFAULT NEXTVAL('users_roles_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    hub_id      BIGINT REFERENCES hubs(id) ON DELETE CASCADE NOT NULL,
    role_status role_status NOT NULL,
    CONSTRAINT user_role_uid UNIQUE(user_id, hub_id)
);

-- posts --

CREATE SEQUENCE posts_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE post_status AS ENUM ('todo', 'doing', 'done', 'trash');

CREATE TABLE IF NOT EXISTS posts (
    id          BIGINT DEFAULT NEXTVAL('posts_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    hub_id      BIGINT REFERENCES hubs(id) ON DELETE CASCADE NOT NULL,
    post_status post_status NOT NULL,
    post_title  VARCHAR(255) NOT NULL
);

-- post tags --

CREATE SEQUENCE posts_tags_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_tags (
    id          BIGINT DEFAULT NEXTVAL('posts_tags_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    post_id     BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    tag_value   VARCHAR(255) NOT NULL,
    CONSTRAINT post_tag_uid UNIQUE(post_id, tag_value)
);

-- posts meta --

CREATE SEQUENCE posts_meta_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_meta (
    id          BIGINT DEFAULT NEXTVAL('posts_meta_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    post_id     BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    meta_key    VARCHAR(20)  NOT NULL,
    meta_value  VARCHAR(255) NOT NULL,
    CONSTRAINT post_meta_uid UNIQUE(post_id, meta_key)
);

-- posts comments --

CREATE SEQUENCE posts_comments_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_comments (
    id              BIGINT DEFAULT NEXTVAL('posts_comments_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id         BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    post_id         BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    comment_content TEXT NOT NULL
);

-- uploads --

CREATE SEQUENCE uploads_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS uploads (
    id          BIGINT DEFAULT NEXTVAL('uploads_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT '0001-01-01 00:00:00',
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    comment_id  BIGINT REFERENCES posts_comments(id) ON DELETE SET NULL,
    upload_name VARCHAR(255) NOT NULL,
    upload_file VARCHAR(255) NOT NULL UNIQUE,
    upload_mime VARCHAR(255) NOT NULL,
    upload_size INT NOT NULL
);

-- role insert --

CREATE FUNCTION role_insert() RETURNS trigger AS $role_insert$
    DECLARE
        roles_count integer;
    BEGIN
        -- users meta
        SELECT COUNT(id) INTO roles_count FROM users_roles WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_meta WHERE user_id = NEW.user_id AND meta_key = 'roles_count') THEN
            UPDATE users_meta SET meta_value = roles_count WHERE user_id = NEW.user_id AND meta_key = 'roles_count';
        ELSE
            INSERT INTO users_meta (user_id, meta_key, meta_value) VALUES (NEW.user_id, 'roles_count', roles_count);
        END IF;
        -- hubs meta
        SELECT COUNT(id) INTO roles_count FROM users_roles WHERE hub_id = NEW.hub_id;
        IF EXISTS (SELECT id FROM hubs_meta WHERE hub_id = NEW.hub_id AND meta_key = 'roles_count') THEN
            UPDATE hubs_meta SET meta_value = roles_count WHERE hub_id = NEW.hub_id AND meta_key = 'roles_count';
        ELSE
            INSERT INTO hubs_meta (hub_id, meta_key, meta_value) VALUES (NEW.hub_id, 'roles_count', roles_count);
        END IF;
        --
        RETURN NEW;
    END;
$role_insert$ LANGUAGE plpgsql;

CREATE TRIGGER role_insert AFTER INSERT ON users_roles FOR EACH ROW EXECUTE PROCEDURE role_insert();

-- role delete --

CREATE FUNCTION role_delete() RETURNS trigger AS $role_delete$
    DECLARE
        roles_count integer;
    BEGIN
        -- users meta
        SELECT COUNT(id) INTO roles_count FROM users_roles WHERE user_id = NEW.user_id;
        IF roles_count = 0 THEN
            DELETE FROM users_meta WHERE user_id = OLD.user_id AND meta_key = 'roles_count';
        ELSE
            UPDATE users_meta SET meta_value = roles_count WHERE user_id = OLD.user_id AND meta_key = 'roles_count';
        END IF;
        -- hubs meta
        SELECT COUNT(id) INTO roles_count FROM users_roles WHERE hub_id = NEW.hub_id;
        IF roles_count = 0 THEN
            DELETE FROM hubs_meta WHERE hub_id = OLD.hub_id AND meta_key = 'roles_count';
        ELSE
            UPDATE hubs_meta SET meta_value = roles_count WHERE hub_id = OLD.hub_id AND meta_key = 'roles_count';
        END IF;
        --
        RETURN NEW;
    END;
$role_delete$ LANGUAGE plpgsql;

CREATE TRIGGER role_delete AFTER DELETE ON users_roles FOR EACH ROW EXECUTE PROCEDURE role_delete();

-- post insert --

CREATE FUNCTION post_insert() RETURNS trigger AS $post_insert$
    DECLARE
        posts_count integer;
    BEGIN
        -- hubs meta
        SELECT COUNT(id) INTO posts_count FROM posts WHERE hub_id = NEW.hub_id;
        IF EXISTS (SELECT id FROM hubs_meta WHERE hub_id = NEW.hub_id AND meta_key = 'posts_count') THEN
            UPDATE hubs_meta SET meta_value = posts_count WHERE hub_id = NEW.hub_id AND meta_key = 'posts_count';
        ELSE
            INSERT INTO hubs_meta (hub_id, meta_key, meta_value) VALUES (NEW.hub_id, 'posts_count', posts_count);
        END IF;
        --
        RETURN NEW;
    END;
$post_insert$ LANGUAGE plpgsql;

CREATE TRIGGER post_insert AFTER INSERT ON posts FOR EACH ROW EXECUTE PROCEDURE post_insert();

-- post delete --

CREATE FUNCTION post_delete() RETURNS trigger AS $post_delete$
    DECLARE
        posts_count integer;
    BEGIN
        -- hubs meta
        SELECT COUNT(id) INTO posts_count FROM posts WHERE hub_id = NEW.hub_id;
        IF posts_count = 0 THEN
            DELETE FROM hubs_meta WHERE hub_id = OLD.hub_id AND meta_key = 'posts_count';
        ELSE
            UPDATE hubs_meta SET meta_value = posts_count WHERE hub_id = OLD.hub_id AND meta_key = 'posts_count';
        END IF;
        --
        RETURN NEW;
    END;
$post_delete$ LANGUAGE plpgsql;

CREATE TRIGGER post_delete AFTER DELETE ON posts FOR EACH ROW EXECUTE PROCEDURE post_delete();

-- comment insert --

CREATE FUNCTION comment_insert() RETURNS trigger AS $comment_insert$
    DECLARE
        comments_count integer;
        comments_increment integer;
        tmp integer[];
    BEGIN
        -- post meta
        SELECT COUNT(id) INTO comments_count FROM comments WHERE post_id = NEW.post_id;
        IF EXISTS (SELECT id FROM posts_meta WHERE post_id = NEW.post_id AND meta_key = 'comments_count') THEN
            UPDATE posts_meta SET meta_value = comments_count WHERE post_id = NEW.post_id AND meta_key = 'comments_count';
        ELSE
            INSERT INTO posts_meta (post_id, meta_key, meta_value) VALUES (NEW.post_id, 'comments_count', comments_count);
        END IF;
        -- users meta
        SELECT id INTO tmp FROM users WHERE id IN (SELECT user_id FROM users_roles WHERE hub_id IN ());

        SELECT user_id INTO tmp FROM users_roles WHERE hub_id IN 
            (SELECT hub_id FROM posts WHERE id IN 
                (SELECT post_id FROM comments WHERE id = NEW.id))
        
        
        RETURN NEW;
    END;
$comment_insert$ LANGUAGE plpgsql;

CREATE TRIGGER comment_insert AFTER INSERT ON posts_comments FOR EACH ROW EXECUTE PROCEDURE comment_insert();

-- data --

INSERT INTO users (user_status, user_token, user_email, user_hash, user_name) VALUES ('approved', '11111111111111111111111111111111111111111111111111111111111111111111111111111111', '14november@mail.ru', '', 'art abramov');
INSERT INTO users (user_status, user_token, user_email, user_hash, user_name) VALUES ('approved', '22222222222222222222222222222222222222222222222222222222222222222222222222222222', 'notdepot@gmail.com', '', 'not depot');
INSERT INTO hubs (user_id, hub_status, hub_name) VALUES (1, 'custom', 'first hub');
INSERT INTO hubs (user_id, hub_status, hub_name) VALUES (2, 'custom', 'second hub');
INSERT INTO users_roles (user_id, hub_id, role_status) VALUES (1, 1, 'admin');
INSERT INTO users_roles (user_id, hub_id, role_status) VALUES (2, 2, 'admin');
INSERT INTO posts (user_id, hub_id, post_status, post_title) VALUES (1, 1, 'todo', 'first post');
INSERT INTO posts (user_id, hub_id, post_status, post_title) VALUES (2, 2, 'todo', 'second post');

-- drop all --

DROP TABLE IF EXISTS users_meta;
DROP TABLE IF EXISTS users_roles;
DROP TABLE IF EXISTS users_vols;
DROP TABLE IF EXISTS hubs_meta;
DROP TABLE IF EXISTS posts_meta;
DROP TABLE IF EXISTS posts_tags;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS posts_comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS hubs;
DROP TABLE IF EXISTS users;

DROP TYPE IF EXISTS user_status;
DROP TYPE IF EXISTS hub_status;
DROP TYPE IF EXISTS role_status;
DROP TYPE IF EXISTS post_status;

DROP SEQUENCE IF EXISTS users_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_roles_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_meta_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_vols_id_seq CASCADE;
DROP SEQUENCE IF EXISTS hubs_id_seq CASCADE;
DROP SEQUENCE IF EXISTS hubs_meta_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_meta_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_tags_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_comments_id_seq CASCADE;
DROP SEQUENCE IF EXISTS uploads_id_seq CASCADE;

DROP TRIGGER IF EXISTS role_insert ON users_roles;
DROP TRIGGER IF EXISTS role_delete ON users_roles;
DROP TRIGGER IF EXISTS post_insert ON posts;
DROP TRIGGER IF EXISTS post_delete ON posts;

DROP FUNCTION IF EXISTS role_insert;
DROP FUNCTION IF EXISTS role_delete;
DROP FUNCTION IF EXISTS post_insert;
DROP FUNCTION IF EXISTS post_delete;

-- select all

\pset format wrapped
SELECT * FROM users; SELECT * FROM users_meta; SELECT * FROM users_vols; SELECT * FROM users_roles; SELECT * FROM hubs; SELECT * FROM hubs_meta; SELECT * FROM posts; SELECT * FROM posts_meta; SELECT * FROM posts_tags; SELECT * FROM posts_comments; SELECT * FROM uploads; 

-- ...



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









