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
    user_phone  VARCHAR(40) NULL UNIQUE,
    user_hash   VARCHAR(40) NULL,
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

-- alert insert --

CREATE FUNCTION alert_insert() RETURNS trigger AS $alert_insert$
    DECLARE
        alerts_sum INTEGER;
    BEGIN
        -- users meta
        SELECT SUM(alerts_count) INTO alerts_sum FROM users_alerts WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_meta WHERE user_id = NEW.user_id AND meta_key = 'alerts_sum') THEN
            UPDATE users_meta SET meta_value = alerts_sum WHERE user_id = NEW.user_id AND meta_key = 'alerts_sum';
        ELSE
            INSERT INTO users_meta (user_id, meta_key, meta_value) VALUES (NEW.user_id, 'alerts_sum', alerts_sum);
        END IF;
        --
        RETURN NEW;
    END;
$alert_insert$ LANGUAGE plpgsql;

CREATE TRIGGER alert_insert AFTER INSERT ON users_alerts FOR EACH ROW EXECUTE PROCEDURE alert_insert();

-- alert delete --

CREATE FUNCTION alert_delete() RETURNS trigger AS $alert_delete$
    DECLARE
        alerts_sum INTEGER;
    BEGIN
        -- users meta
        SELECT SUM(alerts_count) INTO alerts_sum FROM users_alerts WHERE user_id = OLD.user_id;
        IF alerts_sum IS NULL THEN
            DELETE FROM users_meta WHERE user_id = OLD.user_id AND meta_key = 'alerts_sum';
        ELSE
            UPDATE users_meta SET meta_value = alerts_sum WHERE user_id = OLD.user_id AND meta_key = 'alerts_sum';
        END IF;
        --
        RETURN OLD;
    END;
$alert_delete$ LANGUAGE plpgsql;

CREATE TRIGGER alert_delete AFTER DELETE ON users_alerts FOR EACH ROW EXECUTE PROCEDURE alert_delete();

-- alert update --

CREATE FUNCTION alert_update() RETURNS trigger AS $alert_update$
    DECLARE
        alerts_sum INTEGER;
    BEGIN
        -- users meta
        SELECT SUM(alerts_count) INTO alerts_sum FROM users_alerts WHERE user_id = OLD.user_id;
        UPDATE users_meta SET meta_value = alerts_sum WHERE user_id = OLD.user_id AND meta_key = 'alerts_sum';
        --
        RETURN OLD;
    END;
$alert_update$ LANGUAGE plpgsql;

CREATE TRIGGER alert_update AFTER UPDATE ON users_alerts FOR EACH ROW EXECUTE PROCEDURE alert_update();

-- view: users relations --

CREATE OR REPLACE VIEW vw_users_relations AS
    SELECT DISTINCT users_roles.user_id AS user_id, users.id AS to_id FROM users_roles
    JOIN hubs ON hubs.id = users_roles.hub_id
    JOIN users ON users.id IN (SELECT users_roles.user_id FROM users_roles WHERE users_roles.hub_id = hubs.id)
    WHERE users.id <> users_roles.user_id
    ORDER BY users_roles.user_id, users.id;

-- view: users vols --

CREATE OR REPLACE VIEW vw_users_vols AS
    SELECT users.id AS user_id, users_vols.id AS vol_id FROM users
    JOIN users_vols ON users.id = users_vols.user_id
    WHERE users_vols.expire_date >= NOW()
    ORDER BY users_vols.vol_size DESC
    LIMIT 1;
    
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

DROP VIEW IF EXISTS vw_users_relations;

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
DROP TRIGGER IF EXISTS alert_insert ON users_alerts;
DROP TRIGGER IF EXISTS alert_delete ON users_alerts;
DROP TRIGGER IF EXISTS alert_update ON users_alerts;

DROP FUNCTION IF EXISTS role_insert;
DROP FUNCTION IF EXISTS role_delete;
DROP FUNCTION IF EXISTS post_insert;
DROP FUNCTION IF EXISTS post_delete;
DROP FUNCTION IF EXISTS comment_insert;
DROP FUNCTION IF EXISTS comment_delete;
DROP FUNCTION IF EXISTS upload_insert;
DROP FUNCTION IF EXISTS upload_delete;
DROP FUNCTION IF EXISTS alert_insert;
DROP FUNCTION IF EXISTS alert_delete;
DROP FUNCTION IF EXISTS alert_update;

-- erase --

DELETE FROM users_meta;
DELETE FROM users_roles;
DELETE FROM users_vols;
DELETE FROM users_alerts;
DELETE FROM hubs_meta;
DELETE FROM posts_meta;
DELETE FROM posts_tags;
DELETE FROM uploads;
DELETE FROM posts_comments;
DELETE FROM posts;
DELETE FROM hubs;
DELETE FROM users;

-- select all

\pset format wrapped
SELECT * FROM users; SELECT * FROM users_meta; SELECT * FROM users_vols; SELECT * FROM users_roles; SELECT * FROM users_alerts; SELECT * FROM hubs; SELECT * FROM hubs_meta; SELECT * FROM posts; SELECT * FROM posts_meta; SELECT * FROM posts_tags; SELECT * FROM posts_comments; SELECT * FROM uploads; 
SELECT * FROM vw_users_relations; SELECT * FROM vw_users_vols;
