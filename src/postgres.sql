-- users --

CREATE SEQUENCE users_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE user_status AS ENUM ('pending', 'approved', 'trash');


CREATE TABLE IF NOT EXISTS users (
    id          BIGINT DEFAULT NEXTVAL('users_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    remind_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
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
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
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
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    expire_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    vol_size    INT NOT NULL
);

-- hubs --

CREATE SEQUENCE hubs_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE hub_status AS ENUM ('custom', 'trash');

CREATE TABLE IF NOT EXISTS hubs (
    id          BIGINT DEFAULT NEXTVAL('hubs_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    hub_status  hub_status NOT NULL,
    hub_name    VARCHAR(128) NOT NULL
);

-- hubs meta --

CREATE SEQUENCE hubs_meta_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS hubs_meta (
    id          BIGINT DEFAULT NEXTVAL('hubs_meta_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
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
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
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
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    hub_id      BIGINT REFERENCES hubs(id) ON DELETE CASCADE NOT NULL,
    post_status post_status NOT NULL,
    post_title  VARCHAR(255) NOT NULL
);

-- users alerts --

CREATE SEQUENCE users_alerts_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS users_alerts (
    id           BIGINT DEFAULT NEXTVAL('users_alerts_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id      BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    post_id      BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    alerts_count INT NOT NULL,
    CONSTRAINT user_alert_uid UNIQUE(user_id, post_id)
);

-- post tags --

CREATE SEQUENCE posts_tags_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_tags (
    id          BIGINT DEFAULT NEXTVAL('posts_tags_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    post_id     BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    tag_value   VARCHAR(255) NOT NULL,
    CONSTRAINT post_tag_uid UNIQUE(post_id, tag_value)
);

-- posts meta --

CREATE SEQUENCE posts_meta_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_meta (
    id          BIGINT DEFAULT NEXTVAL('posts_meta_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
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
    update_date     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id         BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    post_id         BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    comment_content TEXT NOT NULL
);

-- uploads --

CREATE SEQUENCE uploads_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS uploads (
    id          BIGINT DEFAULT NEXTVAL('uploads_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
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
        SELECT COUNT(id) INTO roles_count FROM users_roles WHERE user_id = OLD.user_id;
        IF roles_count = 0 THEN
            DELETE FROM users_meta WHERE user_id = OLD.user_id AND meta_key = 'roles_count';
        ELSE
            UPDATE users_meta SET meta_value = roles_count WHERE user_id = OLD.user_id AND meta_key = 'roles_count';
        END IF;
        -- hubs meta
        SELECT COUNT(id) INTO roles_count FROM users_roles WHERE hub_id = OLD.hub_id;
        IF roles_count = 0 THEN
            DELETE FROM hubs_meta WHERE hub_id = OLD.hub_id AND meta_key = 'roles_count';
        ELSE
            UPDATE hubs_meta SET meta_value = roles_count WHERE hub_id = OLD.hub_id AND meta_key = 'roles_count';
        END IF;
        --
        RETURN OLD;
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
        SELECT COUNT(id) INTO posts_count FROM posts WHERE hub_id = OLD.hub_id;
        IF posts_count = 0 THEN
            DELETE FROM hubs_meta WHERE hub_id = OLD.hub_id AND meta_key = 'posts_count';
        ELSE
            UPDATE hubs_meta SET meta_value = posts_count WHERE hub_id = OLD.hub_id AND meta_key = 'posts_count';
        END IF;
        --
        RETURN OLD;
    END;
$post_delete$ LANGUAGE plpgsql;

CREATE TRIGGER post_delete AFTER DELETE ON posts FOR EACH ROW EXECUTE PROCEDURE post_delete();

-- comment insert --

CREATE FUNCTION comment_insert() RETURNS trigger AS $comment_insert$
    DECLARE
        comments_count integer;
        i integer;
    BEGIN
        -- post meta
        SELECT COUNT(id) INTO comments_count FROM posts_comments WHERE post_id = NEW.post_id;
        IF EXISTS (SELECT id FROM posts_meta WHERE post_id = NEW.post_id AND meta_key = 'comments_count') THEN
            UPDATE posts_meta SET meta_value = comments_count WHERE post_id = NEW.post_id AND meta_key = 'comments_count';
        ELSE
            INSERT INTO posts_meta (post_id, meta_key, meta_value) VALUES (NEW.post_id, 'comments_count', comments_count);
        END IF;
        -- users alerts
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.hub_id IN (
                SELECT posts.hub_id FROM posts WHERE posts.id = NEW.post_id)
        LOOP
            IF EXISTS (SELECT id FROM users_alerts WHERE user_id = i AND post_id = NEW.post_id) THEN
                UPDATE users_alerts SET alerts_count = users_alerts.alerts_count + 1 WHERE user_id = i AND post_id = NEW.post_id;
            ELSE
                INSERT INTO users_alerts (user_id, post_id, alerts_count) VALUES (i, NEW.post_id, 1);
            END IF;

        END LOOP;
        
        RETURN NEW;
    END;
$comment_insert$ LANGUAGE plpgsql;

CREATE TRIGGER comment_insert AFTER INSERT ON posts_comments FOR EACH ROW EXECUTE PROCEDURE comment_insert();

-- comment delete --

CREATE FUNCTION comment_delete() RETURNS trigger AS $comment_delete$
    DECLARE
        comments_count INTEGER;
        alerts_count INTEGER;
        i INTEGER;
    BEGIN
        -- post meta
        SELECT COUNT(id) INTO comments_count FROM posts_comments WHERE post_id = OLD.post_id;

        IF comments_count = 0 THEN
            DELETE FROM posts_meta WHERE post_id = OLD.post_id AND meta_key = 'comments_count';
        ELSE
            UPDATE posts_meta SET meta_value = comments_count WHERE post_id = OLD.post_id AND meta_key = 'comments_count';
        END IF;

        -- users alerts
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.hub_id IN (
                SELECT posts.hub_id FROM posts WHERE posts.id = OLD.post_id)
        LOOP
            SELECT users_alerts.alerts_count INTO alerts_count FROM users_alerts WHERE user_id = i AND post_id = OLD.post_id;
            IF alerts_count = 1 THEN
                DELETE FROM users_alerts WHERE user_id = i AND post_id = OLD.post_id;
            ELSIF alerts_count > 1 THEN
                UPDATE users_alerts SET alerts_count = users_alerts.alerts_count - 1 WHERE user_id = i AND post_id = OLD.post_id;
            END IF;

        END LOOP;
        
        RETURN OLD;
    END;
$comment_delete$ LANGUAGE plpgsql;

CREATE TRIGGER comment_delete AFTER DELETE ON posts_comments FOR EACH ROW EXECUTE PROCEDURE comment_delete();

-- upload insert --

CREATE FUNCTION upload_insert() RETURNS trigger AS $upload_insert$
    DECLARE
        uploads_sum INTEGER;
    BEGIN
        -- users meta
        SELECT SUM(upload_size) INTO uploads_sum FROM uploads WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_meta WHERE user_id = NEW.user_id AND meta_key = 'uploads_sum') THEN
            UPDATE users_meta SET meta_value = uploads_sum WHERE user_id = NEW.user_id AND meta_key = 'uploads_sum';
        ELSE
            INSERT INTO users_meta (user_id, meta_key, meta_value) VALUES (NEW.user_id, 'uploads_sum', uploads_sum);
        END IF;
        --
        RETURN NEW;
    END;
$upload_insert$ LANGUAGE plpgsql;

CREATE TRIGGER upload_insert AFTER INSERT ON uploads FOR EACH ROW EXECUTE PROCEDURE upload_insert();

-- upload delete --

CREATE FUNCTION upload_delete() RETURNS trigger AS $upload_delete$
    DECLARE
        uploads_sum INTEGER;
    BEGIN
        -- users meta
        SELECT SUM(upload_size) INTO uploads_sum FROM uploads WHERE user_id = OLD.user_id;
        IF uploads_sum IS NULL THEN
            DELETE FROM users_meta WHERE user_id = OLD.user_id AND meta_key = 'uploads_sum';
        ELSE
            UPDATE users_meta SET meta_value = uploads_sum WHERE user_id = OLD.user_id AND meta_key = 'uploads_sum';
        END IF;
        --
        RETURN OLD;
    END;
$upload_delete$ LANGUAGE plpgsql;

CREATE TRIGGER upload_delete AFTER DELETE ON uploads FOR EACH ROW EXECUTE PROCEDURE upload_delete();

-- view: users pals --

CREATE OR REPLACE VIEW vw_users_pals AS
    SELECT DISTINCT users_roles.user_id AS user_id, users.id AS pal_id FROM users_roles
    JOIN hubs ON hubs.id = users_roles.hub_id
    JOIN users ON users.id IN (SELECT users_roles.user_id FROM users_roles WHERE users_roles.hub_id = hubs.id)
    WHERE users.id <> users_roles.user_id
    ORDER BY users_roles.user_id, users.id;

-- data --

INSERT INTO users (id, user_status, user_token, user_email, user_hash, user_name) VALUES (1, 'approved', '11111111111111111111111111111111111111111111111111111111111111111111111111111111', '14november@mail.ru', '', 'art abramov');
INSERT INTO users (id, user_status, user_token, user_email, user_hash, user_name) VALUES (2, 'approved', '22222222222222222222222222222222222222222222222222222222222222222222222222222222', 'notdepot@gmail.com', '', 'not depot');
INSERT INTO users (id, user_status, user_token, user_email, user_hash, user_name) VALUES (3, 'approved', '33333333333333333333333333333333333333333333333333333333333333333333333333333333', 'strangerb@gmail.com', '', 'stranger b');
INSERT INTO users (id, user_status, user_token, user_email, user_hash, user_name) VALUES (4, 'approved', '44444444444444444444444444444444444444444444444444444444444444444444444444444444', 'strangerc@gmail.com', '', 'stranger c');
INSERT INTO hubs (id, user_id, hub_status, hub_name) VALUES (1, 1, 'custom', 'first hub');
INSERT INTO hubs (id, user_id, hub_status, hub_name) VALUES (2, 2, 'custom', 'second hub');
INSERT INTO hubs (id, user_id, hub_status, hub_name) VALUES (3, 3, 'custom', 'third hub');
INSERT INTO hubs (id, user_id, hub_status, hub_name) VALUES (4, 3, 'custom', 'fourth hub');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (1, 1, 1, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (2, 2, 2, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (3, 2, 3, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (4, 3, 1, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (5, 3, 2, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (6, 3, 3, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (7, 3, 4, 'admin');
INSERT INTO users_roles (id, user_id, hub_id, role_status) VALUES (8, 4, 4, 'admin');
INSERT INTO posts (id, user_id, hub_id, post_status, post_title) VALUES (1, 1, 1, 'todo', 'first post');
INSERT INTO posts (id, user_id, hub_id, post_status, post_title) VALUES (2, 2, 2, 'todo', 'second post');
INSERT INTO posts_comments (id, user_id, post_id, comment_content) VALUES (1, 1, 1, 'first comment');
INSERT INTO posts_comments (id, user_id, post_id, comment_content) VALUES (2, 1, 1, 'second comment');
INSERT INTO posts_comments (id, user_id, post_id, comment_content) VALUES (3, 1, 1, 'third comment');
INSERT INTO posts_comments (id, user_id, post_id, comment_content) VALUES (4, 1, 1, 'fourth comment');
INSERT INTO uploads (id, user_id, comment_id, upload_name, upload_file, upload_mime, upload_size) VALUES (1, 1, 1, 'file1.txt', 'uploads/file1.txt', 'plain/text', 100);
INSERT INTO uploads (id, user_id, comment_id, upload_name, upload_file, upload_mime, upload_size) VALUES (2, 1, 1, 'file2.txt', 'uploads/file2.txt', 'plain/text', 100);
INSERT INTO uploads (id, user_id, comment_id, upload_name, upload_file, upload_mime, upload_size) VALUES (3, 1, 1, 'file3.txt', 'uploads/file3.txt', 'plain/text', 100);

-- drop all --

DROP VIEW IF EXISTS vw_users_pals;

DROP TABLE IF EXISTS users_meta;
DROP TABLE IF EXISTS users_roles;
DROP TABLE IF EXISTS users_vols;
DROP TABLE IF EXISTS users_alerts;
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
DROP SEQUENCE IF EXISTS users_alerts_id_seq CASCADE;
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
DROP TRIGGER IF EXISTS comment_insert ON posts_comments;
DROP TRIGGER IF EXISTS comment_delete ON posts_comments;
DROP TRIGGER IF EXISTS upload_insert ON uploads;
DROP TRIGGER IF EXISTS upload_delete ON uploads;

DROP FUNCTION IF EXISTS role_insert;
DROP FUNCTION IF EXISTS role_delete;
DROP FUNCTION IF EXISTS post_insert;
DROP FUNCTION IF EXISTS post_delete;
DROP FUNCTION IF EXISTS comment_insert;
DROP FUNCTION IF EXISTS comment_delete;
DROP FUNCTION IF EXISTS upload_insert;
DROP FUNCTION IF EXISTS upload_delete;

-- select all

\pset format wrapped
SELECT * FROM users; SELECT * FROM users_meta; SELECT * FROM users_vols; SELECT * FROM users_roles; SELECT * FROM users_alerts; SELECT * FROM hubs; SELECT * FROM hubs_meta; SELECT * FROM posts; SELECT * FROM posts_meta; SELECT * FROM posts_tags; SELECT * FROM posts_comments; SELECT * FROM uploads; 
SELECT * FROM vw_users_pals;

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









