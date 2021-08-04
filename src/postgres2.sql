-- drop all --

DROP VIEW IF EXISTS vw_users_relations;

DROP TABLE IF EXISTS users_terms;
DROP TABLE IF EXISTS users_roles;
DROP TABLE IF EXISTS users_alerts;
DROP TABLE IF EXISTS users_volumes;
DROP TABLE IF EXISTS repos_terms;
DROP TABLE IF EXISTS posts_terms;
DROP TABLE IF EXISTS posts_tags;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS repos;
DROP TABLE IF EXISTS users;

DROP TYPE IF EXISTS user_status;
DROP TYPE IF EXISTS role_status;
DROP TYPE IF EXISTS post_status;

DROP SEQUENCE IF EXISTS users_terms_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_roles_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_alerts_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_volumes_id_seq CASCADE;
DROP SEQUENCE IF EXISTS repos_terms_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_terms_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_tags_id_seq CASCADE;
DROP SEQUENCE IF EXISTS uploads_id_seq CASCADE;
DROP SEQUENCE IF EXISTS comments_id_seq CASCADE;
DROP SEQUENCE IF EXISTS posts_id_seq CASCADE;
DROP SEQUENCE IF EXISTS repos_id_seq CASCADE;
DROP SEQUENCE IF EXISTS users_id_seq CASCADE;

DROP TRIGGER IF EXISTS role_insert ON users_roles;
DROP TRIGGER IF EXISTS role_delete ON users_roles;
DROP TRIGGER IF EXISTS post_insert ON posts;
DROP TRIGGER IF EXISTS post_delete ON posts;
DROP TRIGGER IF EXISTS post_update ON posts;
DROP TRIGGER IF EXISTS comment_insert ON posts_comments;
DROP TRIGGER IF EXISTS comment_delete ON posts_comments;
--DROP TRIGGER IF EXISTS upload_insert ON uploads;
--DROP TRIGGER IF EXISTS upload_delete ON uploads;
--DROP TRIGGER IF EXISTS alert_insert ON users_alerts;
--DROP TRIGGER IF EXISTS alert_delete ON users_alerts;
--DROP TRIGGER IF EXISTS alert_update ON users_alerts;

DROP FUNCTION IF EXISTS role_insert;
DROP FUNCTION IF EXISTS role_delete;
DROP FUNCTION IF EXISTS post_insert;
DROP FUNCTION IF EXISTS post_delete;
DROP FUNCTION IF EXISTS post_update;
DROP FUNCTION IF EXISTS comment_insert;
DROP FUNCTION IF EXISTS comment_delete;
--DROP FUNCTION IF EXISTS upload_insert;
--DROP FUNCTION IF EXISTS upload_delete;
--DROP FUNCTION IF EXISTS alert_insert;
--DROP FUNCTION IF EXISTS alert_delete;
--DROP FUNCTION IF EXISTS alert_update;

-- table: users --

CREATE SEQUENCE users_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE user_status AS ENUM ('pending', 'approved', 'trash');

CREATE TABLE IF NOT EXISTS users (
    id          BIGINT DEFAULT NEXTVAL('users_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    remind_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    auth_date   TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_status user_status,
    user_token  CHAR(80) NOT NULL UNIQUE,
    user_email  VARCHAR(255) NOT NULL UNIQUE,
    user_phone  VARCHAR(40) NULL UNIQUE,
    user_hash   VARCHAR(40) NULL,
    user_name   VARCHAR(128) NOT NULL
);

-- table: users_terms --

CREATE SEQUENCE users_terms_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS users_terms (
    id          BIGINT DEFAULT NEXTVAL('users_terms_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    term_key    VARCHAR(20)  NOT NULL,
    term_value  VARCHAR(255) NOT NULL,
    CONSTRAINT user_terms_uid UNIQUE(user_id, term_key)
);

-- table: users_volumes --

CREATE SEQUENCE users_volumes_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS users_volumes (
    id          BIGINT DEFAULT NEXTVAL('users_volumes_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    expire_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    volume_size INT NOT NULL
);

-- table: repos --

CREATE SEQUENCE repos_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS repos (
    id          BIGINT DEFAULT NEXTVAL('repos_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    repo_name   VARCHAR(128) NOT NULL
);

-- table: repos_terms --

CREATE SEQUENCE repos_terms_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS repos_terms (
    id          BIGINT DEFAULT NEXTVAL('repos_terms_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    repo_id     BIGINT REFERENCES repos(id) ON DELETE CASCADE NOT NULL,
    term_key    VARCHAR(20)  NOT NULL,
    term_value  VARCHAR(255) NOT NULL,
    CONSTRAINT repo_term_uid UNIQUE(repo_id, term_key)
);

-- table: users_roles --

CREATE SEQUENCE users_roles_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE role_status AS ENUM ('admin', 'editor', 'reader');

CREATE TABLE IF NOT EXISTS users_roles (
    id          BIGINT DEFAULT NEXTVAL('users_roles_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    repo_id     BIGINT REFERENCES repos(id) ON DELETE CASCADE NOT NULL,
    role_status role_status NOT NULL,
    CONSTRAINT user_role_uid UNIQUE(user_id, repo_id)
);

-- table: posts --

CREATE SEQUENCE posts_id_seq START WITH 1 INCREMENT BY 1;
CREATE TYPE post_status AS ENUM ('todo', 'doing', 'done');

CREATE TABLE IF NOT EXISTS posts (
    id          BIGINT DEFAULT NEXTVAL('posts_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    repo_id     BIGINT REFERENCES repos(id) ON DELETE CASCADE NOT NULL,
    post_status post_status NOT NULL,
    post_title  VARCHAR(255) NOT NULL
);

-- table: users_alerts --

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

-- table: posts_terms --

CREATE SEQUENCE posts_terms_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_terms (
    id          BIGINT DEFAULT NEXTVAL('posts_terms_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    post_id     BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    term_key    VARCHAR(20)  NOT NULL,
    term_value  VARCHAR(255) NOT NULL,
    CONSTRAINT post_term_uid UNIQUE(post_id, term_key)
);

-- table: post_tags --

CREATE SEQUENCE posts_tags_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS posts_tags (
    id          BIGINT DEFAULT NEXTVAL('posts_tags_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    post_id     BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    tag_value   VARCHAR(255) NOT NULL,
    CONSTRAINT post_tag_uid UNIQUE(post_id, tag_value)
);

-- table: comments --

CREATE SEQUENCE comments_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS comments (
    id              BIGINT DEFAULT NEXTVAL('comments_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id         BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    post_id         BIGINT REFERENCES posts(id) ON DELETE CASCADE NOT NULL,
    comment_content TEXT NOT NULL
);

-- table: uploads --

CREATE SEQUENCE uploads_id_seq START WITH 1 INCREMENT BY 1;

CREATE TABLE IF NOT EXISTS uploads (
    id          BIGINT DEFAULT NEXTVAL('uploads_id_seq'::regclass) NOT NULL PRIMARY KEY,
    create_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()::timestamp(0),
    update_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT to_timestamp(0),
    user_id     BIGINT REFERENCES users(id) ON DELETE NO ACTION NOT NULL,
    comment_id  BIGINT REFERENCES comments(id) ON DELETE SET NULL,
    upload_name VARCHAR(255) NOT NULL,
    upload_file VARCHAR(255) NOT NULL UNIQUE,
    upload_mime VARCHAR(255) NOT NULL,
    upload_size INT NOT NULL
);

-- view: vw_users_relations --

CREATE OR REPLACE VIEW vw_users_relations AS
    SELECT DISTINCT users_roles.user_id AS user_id, users.id AS relate_id FROM users_roles
    JOIN repos ON repos.id = users_roles.repo_id
    JOIN users ON users.id IN (SELECT users_roles.user_id FROM users_roles WHERE users_roles.repo_id = repos.id)
    WHERE users.id <> users_roles.user_id
    ORDER BY users_roles.user_id, users.id;

-- trigger: role insert --

CREATE FUNCTION role_insert() RETURNS trigger AS $role_insert$

    DECLARE
        rol_count INTEGER;
        rel_count INTEGER;
        i INTEGER;
    BEGIN

        -- users terms: roles_count
        SELECT COUNT(id) INTO rol_count FROM users_roles WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_terms WHERE user_id = NEW.user_id AND term_key = 'roles_count') THEN
            UPDATE users_terms SET term_value = rol_count WHERE user_id = NEW.user_id AND term_key = 'roles_count';
        ELSE
            INSERT INTO users_terms (user_id, term_key, term_value) VALUES (NEW.user_id, 'roles_count', rol_count);
        END IF;

        -- repos terms: roles_count
        SELECT COUNT(id) INTO rol_count FROM users_roles WHERE repo_id = NEW.repo_id;
        IF EXISTS (SELECT id FROM repos_terms WHERE repo_id = NEW.repo_id AND term_key = 'roles_count') THEN
            UPDATE repos_terms SET term_value = rol_count WHERE repo_id = NEW.repo_id AND term_key = 'roles_count';
        ELSE
            INSERT INTO repos_terms (repo_id, term_key, term_value) VALUES (NEW.repo_id, 'roles_count', rol_count);
        END IF;

        -- users terms: relations_count
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.repo_id = NEW.repo_id
        LOOP
            SELECT COUNT(relate_id) INTO rel_count FROM vw_users_relations WHERE user_id = i;
            IF EXISTS (SELECT id FROM users_terms WHERE user_id = i AND term_key = 'relations_count') THEN
                UPDATE users_terms SET term_value = rel_count WHERE user_id = i AND term_key = 'relations_count';
            ELSIF rel_count > 0 THEN
                INSERT INTO users_terms (user_id, term_key, term_value) VALUES (i, 'relations_count', rel_count);
            END IF;
        END LOOP;

        --
        RETURN NEW;
    END;
$role_insert$ LANGUAGE plpgsql;

CREATE TRIGGER role_insert AFTER INSERT ON users_roles FOR EACH ROW EXECUTE PROCEDURE role_insert();

-- trigger: role delete --

CREATE FUNCTION role_delete() RETURNS trigger AS $role_delete$
    DECLARE
        rol_count INTEGER;
        rel_count INTEGER;
        i INTEGER;
    BEGIN

        -- users terms: roles_count
        SELECT COUNT(id) INTO rol_count FROM users_roles WHERE user_id = OLD.user_id;
        IF rol_count = 0 THEN
            DELETE FROM users_terms WHERE user_id = OLD.user_id AND term_key = 'roles_count';
        ELSE
            UPDATE users_terms SET term_value = rol_count WHERE user_id = OLD.user_id AND term_key = 'roles_count';
        END IF;

        -- users terms: relations count
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.repo_id = OLD.repo_id
            UNION SELECT id FROM users WHERE id = OLD.user_id
        LOOP
            SELECT COUNT(relate_id) INTO rel_count FROM vw_users_relations WHERE user_id = i;
            IF rel_count = 0 THEN
                DELETE FROM users_terms WHERE user_id = i AND term_key = 'relations_count';
            ELSE
                UPDATE users_terms SET term_value = rel_count WHERE user_id = i AND term_key = 'relations_count';
            END IF;
        END LOOP;

        -- repos terms: roles_count
        SELECT COUNT(id) INTO rol_count FROM users_roles WHERE repo_id = OLD.repo_id;
        IF rol_count = 0 THEN
            DELETE FROM repos_terms WHERE repo_id = OLD.repo_id AND term_key = 'roles_count';
        ELSE
            UPDATE repos_terms SET term_value = rol_count WHERE repo_id = OLD.repo_id AND term_key = 'roles_count';
        END IF;

        --
        RETURN OLD;
    END;
$role_delete$ LANGUAGE plpgsql;

CREATE TRIGGER role_delete AFTER DELETE ON users_roles FOR EACH ROW EXECUTE PROCEDURE role_delete();

-- trigger: post insert --

CREATE FUNCTION post_insert() RETURNS trigger AS $post_insert$
    DECLARE
        pos_count INTEGER;
        ter_key VARCHAR;
    BEGIN

        -- repos terms: todo_count, doing_count, done_count
        SELECT COUNT(id) INTO pos_count FROM posts WHERE repo_id = NEW.repo_id AND post_status = NEW.post_status;
        ter_key = CONCAT(NEW.post_status, '_count');
        IF EXISTS (SELECT id FROM repos_terms WHERE repo_id = NEW.repo_id AND term_key = ter_key) THEN
            UPDATE repos_terms SET term_value = pos_count WHERE repo_id = NEW.repo_id AND term_key = ter_key;
        ELSE
            INSERT INTO repos_terms (repo_id, term_key, term_value) VALUES (NEW.repo_id, ter_key, pos_count);
        END IF;

        --
        RETURN NEW;
    END;
$post_insert$ LANGUAGE plpgsql;

CREATE TRIGGER post_insert AFTER INSERT ON posts FOR EACH ROW EXECUTE PROCEDURE post_insert();

-- trigger: post delete --

CREATE FUNCTION post_delete() RETURNS trigger AS $post_delete$
    DECLARE
        pos_count INTEGER;
        ter_key VARCHAR;
    BEGIN

        -- repos terms: todo_count, doing_count, done_count
        SELECT COUNT(id) INTO pos_count FROM posts WHERE repo_id = OLD.repo_id AND post_status = OLD.post_status;
        ter_key = CONCAT(OLD.post_status, '_count');

        IF pos_count = 0 THEN
            DELETE FROM repos_terms WHERE repo_id = OLD.repo_id AND term_key = ter_key;
        ELSE
            UPDATE repos_terms SET term_value = pos_count WHERE repo_id = OLD.repo_id AND term_key = ter_key;
        END IF;

        --
        RETURN OLD;
    END;
$post_delete$ LANGUAGE plpgsql;

CREATE TRIGGER post_delete AFTER DELETE ON posts FOR EACH ROW EXECUTE PROCEDURE post_delete();

-- post update --

CREATE FUNCTION post_update() RETURNS trigger AS $post_update$
    DECLARE
        pos_count INTEGER;
        ter_key VARCHAR;
    BEGIN

        -- repos terms (prev): todo_count, doing_count, done_coune
        SELECT COUNT(id) INTO pos_count FROM posts WHERE repo_id = OLD.repo_id AND post_status = OLD.post_status;
        ter_key = CONCAT(OLD.post_status, '_count');

        IF pos_count = 0 THEN
            DELETE FROM repos_terms WHERE repo_id = OLD.repo_id AND term_key = ter_key;
        ELSE
            UPDATE repos_terms SET term_value = pos_count WHERE repo_id = OLD.repo_id AND term_key = ter_key;
        END IF;

        -- repos terms (next): todo_count, doing_count, done_count
        SELECT COUNT(id) INTO pos_count FROM posts WHERE repo_id = NEW.repo_id AND post_status = NEW.post_status;
        ter_key = CONCAT(NEW.post_status, '_count');

        IF EXISTS (SELECT id FROM repos_terms WHERE repo_id = NEW.repo_id AND term_key = ter_key) THEN
            UPDATE repos_terms SET term_value = pos_count WHERE repo_id = NEW.repo_id AND term_key = ter_key;
        ELSE
            INSERT INTO repos_terms (repo_id, term_key, term_value) VALUES (NEW.repo_id, ter_key, pos_count);
        END IF;

        --
        RETURN OLD;
    END;
$post_update$ LANGUAGE plpgsql;

CREATE TRIGGER post_update AFTER UPDATE ON posts FOR EACH ROW EXECUTE PROCEDURE post_update();

-- comment insert --

CREATE FUNCTION comment_insert() RETURNS trigger AS $comment_insert$
    DECLARE
        com_count INTEGER;
        ale_count INTEGER;
        i INTEGER;
    BEGIN

        -- post terms: comments_count
        SELECT COUNT(id) INTO com_count FROM comments WHERE post_id = NEW.post_id;
        IF EXISTS (SELECT id FROM posts_terms WHERE post_id = NEW.post_id AND term_key = 'comments_count') THEN
            UPDATE posts_terms SET term_value = com_count WHERE post_id = NEW.post_id AND term_key = 'comments_count';
        ELSE
            INSERT INTO posts_terms (post_id, term_key, term_value) VALUES (NEW.post_id, 'comments_count', com_count);
        END IF;

        -- users alerts
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.user_id <> NEW.user_id AND users_roles.repo_id IN 
                (SELECT posts.repo_id FROM posts WHERE posts.id = NEW.post_id)
        LOOP
            IF EXISTS (SELECT id FROM users_alerts WHERE user_id = i AND post_id = NEW.post_id) THEN
                UPDATE users_alerts SET alerts_count = users_alerts.alerts_count + 1 WHERE user_id = i AND post_id = NEW.post_id;
            ELSE
                INSERT INTO users_alerts (user_id, post_id, alerts_count) VALUES (i, NEW.post_id, 1);
            END IF;

        END LOOP;

        -- users terms: alerts_count
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.user_id <> NEW.user_id AND users_roles.repo_id IN 
                (SELECT posts.repo_id FROM posts WHERE posts.id = NEW.post_id)
        LOOP
            SELECT SUM(alerts_count) INTO ale_count FROM users_alerts WHERE user_id = i;
            IF EXISTS (SELECT id FROM users_terms WHERE user_id = i AND term_key = 'alerts_count') THEN
                UPDATE users_terms SET term_value = ale_count WHERE user_id = i AND term_key = 'alerts_count';
            ELSE
                INSERT INTO users_terms (user_id, term_key, term_value) VALUES (i, 'alerts_count', ale_count);
            END IF;
        END LOOP;
        
        --
        RETURN NEW;
    END;
$comment_insert$ LANGUAGE plpgsql;

CREATE TRIGGER comment_insert AFTER INSERT ON comments FOR EACH ROW EXECUTE PROCEDURE comment_insert();

-- comment delete --

CREATE FUNCTION comment_delete() RETURNS trigger AS $comment_delete$
    DECLARE
        com_count INTEGER;
        ale_count INTEGER;
        i INTEGER;
    BEGIN

        -- posts terms: comments_count
        SELECT COUNT(id) INTO com_count FROM comments WHERE post_id = OLD.post_id;
        IF com_count = 0 THEN
            DELETE FROM posts_terms WHERE post_id = OLD.post_id AND term_key = 'comments_count';
        ELSE
            UPDATE posts_terms SET term_value = com_count WHERE post_id = OLD.post_id AND term_key = 'comments_count';
        END IF;

        -- users alerts
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.user_id <> OLD.user_id AND users_roles.repo_id IN 
                (SELECT posts.repo_id FROM posts WHERE posts.id = OLD.post_id)
        LOOP
            SELECT users_alerts.alerts_count INTO ale_count FROM users_alerts WHERE user_id = i AND post_id = OLD.post_id;
            IF ale_count = 1 THEN
                DELETE FROM users_alerts WHERE user_id = i AND post_id = OLD.post_id;
            ELSIF ale_count > 1 THEN
                UPDATE users_alerts SET alerts_count = users_alerts.alerts_count - 1 WHERE user_id = i AND post_id = OLD.post_id;
            END IF;

        END LOOP;

        -- users terms: alerts_count
        FOR i IN 
            SELECT users_roles.user_id FROM users_roles WHERE users_roles.user_id <> OLD.user_id AND users_roles.repo_id IN 
                (SELECT posts.repo_id FROM posts WHERE posts.id = OLD.post_id)
        LOOP
            SELECT SUM(alerts_count) INTO ale_count FROM users_alerts WHERE user_id = i;
            IF ale_count IS NULL THEN
                DELETE FROM users_terms WHERE user_id = i AND term_key = 'alerts_count';
            ELSE
                UPDATE users_terms SET term_value = ale_count WHERE user_id = i AND term_key = 'alerts_count';
            END IF;
        END LOOP;
        
        --
        RETURN OLD;
    END;
$comment_delete$ LANGUAGE plpgsql;

CREATE TRIGGER comment_delete AFTER DELETE ON comments FOR EACH ROW EXECUTE PROCEDURE comment_delete();





INSERT INTO users (id, create_date, update_date, remind_date, auth_date, user_status, user_token, user_email, user_hash, user_name) VALUES (1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 'approved', '11234567890123456789012345678901234567890123456789012345678901234567890123456789', 'noreply1@noreply.no', '', 'user 1');
INSERT INTO users (id, create_date, update_date, remind_date, auth_date, user_status, user_token, user_email, user_hash, user_name) VALUES (2, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 'approved', '21234567890123456789012345678901234567890123456789012345678901234567890123456789', 'noreply2@noreply.no', '', 'user 2');
INSERT INTO users (id, create_date, update_date, remind_date, auth_date, user_status, user_token, user_email, user_hash, user_name) VALUES (3, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 'approved', '31234567890123456789012345678901234567890123456789012345678901234567890123456789', 'noreply3@noreply.no', '', 'user 3');
INSERT INTO repos (id, create_date, update_date, user_id, repo_name) VALUES (1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 'repo 1 (by user 1)');
INSERT INTO repos (id, create_date, update_date, user_id, repo_name) VALUES (2, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 2, 'repo 2 (by user 2)');
INSERT INTO users_roles (id, create_date, update_date, user_id, repo_id, role_status) VALUES (1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 1, 'admin');
INSERT INTO users_roles (id, create_date, update_date, user_id, repo_id, role_status) VALUES (2, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 2, 2, 'admin');
INSERT INTO users_roles (id, create_date, update_date, user_id, repo_id, role_status) VALUES (3, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 2, 1, 'reader');
INSERT INTO users_roles (id, create_date, update_date, user_id, repo_id, role_status) VALUES (4, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 2, 'reader');
INSERT INTO users_roles (id, create_date, update_date, user_id, repo_id, role_status) VALUES (5, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 3, 1, 'reader');
DELETE FROM users_roles WHERE id = 4;
DELETE FROM users_roles WHERE id = 5;
INSERT INTO posts (id, create_date, update_date, user_id, repo_id, post_status, post_title) VALUES (1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 1, 'todo', 'post 1');
INSERT INTO posts (id, create_date, update_date, user_id, repo_id, post_status, post_title) VALUES (2, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 1, 'todo', 'post 2');
INSERT INTO posts (id, create_date, update_date, user_id, repo_id, post_status, post_title) VALUES (3, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 1, 'todo', 'post 3');
INSERT INTO posts (id, create_date, update_date, user_id, repo_id, post_status, post_title) VALUES (4, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 2, 'done', 'post 4');
DELETE FROM posts WHERE id = 3;
DELETE FROM posts WHERE id = 4;
UPDATE posts SET post_status = 'done' WHERE id = 1;
UPDATE posts SET post_status = 'done' WHERE id = 2;
INSERT INTO comments (id, create_date, update_date, user_id, post_id, comment_content) VALUES (1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 1, 'comment 1');
INSERT INTO comments (id, create_date, update_date, user_id, post_id, comment_content) VALUES (2, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, 1, 'comment 2');
INSERT INTO comments (id, create_date, update_date, user_id, post_id, comment_content) VALUES (3, '1970-01-01 00:00:00', '1970-01-01 00:00:00', 2, 1, 'comment 2');
DELETE FROM comments WHERE id = 2;
DELETE FROM comments WHERE id = 3;

\pset format wrapped
SELECT * FROM users; SELECT * FROM users_terms; SELECT * FROM repos; SELECT * FROM repos_terms; SELECT * FROM users_roles; SELECT * FROM posts; SELECT * FROM posts_terms; SELECT * FROM comments; SELECT * FROM users_alerts;
SELECT * FROM vw_users_relations;

-- TODO: = 0 / IS NULL ?





























-- upload insert --

CREATE FUNCTION upload_insert() RETURNS trigger AS $upload_insert$
    DECLARE
        up_sum INTEGER;
        po_id INTEGER;
        hu_id INTEGER;
    BEGIN
        -- post id
        SELECT posts.id INTO po_id FROM posts
        JOIN posts_comments ON posts.id = posts_comments.post_id 
        WHERE posts_comments.id = NEW.comment_id
        LIMIT 1;

        -- hub id
        SELECT hubs.id FROM hubs INTO hu_id
        JOIN posts ON posts.hub_id = hubs.id
        JOIN posts_comments ON posts.id = posts_comments.post_id 
        WHERE posts_comments.id = NEW.comment_id
        LIMIT 1;

        -- users meta
        SELECT SUM(upload_size) INTO up_sum FROM uploads WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_meta WHERE user_id = NEW.user_id AND meta_key = 'uploads_sum') THEN
            UPDATE users_meta SET meta_value = up_sum WHERE user_id = NEW.user_id AND meta_key = 'uploads_sum';
        ELSE
            INSERT INTO users_meta (user_id, meta_key, meta_value) VALUES (NEW.user_id, 'uploads_sum', up_sum);
        END IF;

        -- post meta
        --SELECT SUM(uploads.upload_size) INTO uploads_sum FROM uploads
        --JOIN posts_comments ON posts_comments.id = uploads.comment_id 
        --JOIN posts ON posts.id = posts_comments.post_id 
        --WHERE posts.id = p_id;

        --IF EXISTS (SELECT id FROM posts_meta WHERE post_id = p_id AND meta_key = 'uploads_sum') THEN
        --    UPDATE posts_meta SET meta_value = uploads_sum WHERE post_id = NEW.post_id AND meta_key = 'uploads_sum';
        --ELSE
        --    INSERT INTO posts_meta (post_id, meta_key, meta_value) VALUES (NEW.post_id, 'uploads_sum', uploads_sum);
        --END IF;

        -- hub meta
        --SELECT SUM(uploads.upload_size) INTO uploads_sum FROM uploads
        --JOIN posts_comments ON posts_comments.id = uploads.comment_id 
        --JOIN posts ON posts.id = posts_comments.post_id 
        --JOIN hubs ON hubs.id = posts.hub_id
        --WHERE hubs.id = h_id;

        --IF EXISTS (SELECT id FROM hubs_meta WHERE hub_id = NEW.hub_id AND meta_key = m_key) THEN
        --    UPDATE hubs_meta SET meta_value = posts_count WHERE hub_id = NEW.hub_id AND meta_key = m_key;
        --ELSE
        --    INSERT INTO hubs_meta (hub_id, meta_key, meta_value) VALUES (NEW.hub_id, m_key, posts_count);
        --END IF;

        --
        RETURN NEW;
    END;
$upload_insert$ LANGUAGE plpgsql;

CREATE TRIGGER upload_insert AFTER INSERT ON uploads FOR EACH ROW EXECUTE PROCEDURE upload_insert();

-- upload delete --

CREATE FUNCTION upload_delete() RETURNS trigger AS $upload_delete$
    DECLARE
        up_sum INTEGER;
    BEGIN

        -- users meta
        SELECT SUM(upload_size) INTO up_sum FROM uploads WHERE user_id = OLD.user_id;
        IF up_sum IS NULL THEN
            DELETE FROM users_meta WHERE user_id = OLD.user_id AND meta_key = 'uploads_sum';
        ELSE
            UPDATE users_meta SET meta_value = up_sum WHERE user_id = OLD.user_id AND meta_key = 'uploads_sum';
        END IF;

        --
        RETURN OLD;
    END;
$upload_delete$ LANGUAGE plpgsql;

CREATE TRIGGER upload_delete AFTER DELETE ON uploads FOR EACH ROW EXECUTE PROCEDURE upload_delete();

-- alert insert --

CREATE FUNCTION alert_insert() RETURNS trigger AS $alert_insert$
    DECLARE
        al_sum INTEGER;
    BEGIN

        -- users meta
        SELECT SUM(alerts_count) INTO al_sum FROM users_alerts WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_meta WHERE user_id = NEW.user_id AND meta_key = 'alerts_sum') THEN
            UPDATE users_meta SET meta_value = al_sum WHERE user_id = NEW.user_id AND meta_key = 'alerts_sum';
        ELSE
            INSERT INTO users_meta (user_id, meta_key, meta_value) VALUES (NEW.user_id, 'alerts_sum', al_sum);
        END IF;

        --
        RETURN NEW;
    END;
$alert_insert$ LANGUAGE plpgsql;

CREATE TRIGGER alert_insert AFTER INSERT ON users_alerts FOR EACH ROW EXECUTE PROCEDURE alert_insert();

-- alert delete --

CREATE FUNCTION alert_delete() RETURNS trigger AS $alert_delete$
    DECLARE
        al_sum INTEGER;
    BEGIN
        -- users meta
        SELECT SUM(alerts_count) INTO al_sum FROM users_alerts WHERE user_id = OLD.user_id;
        IF al_sum IS NULL THEN
            DELETE FROM users_meta WHERE user_id = OLD.user_id AND meta_key = 'alerts_sum';
        ELSE
            UPDATE users_meta SET meta_value = al_sum WHERE user_id = OLD.user_id AND meta_key = 'alerts_sum';
        END IF;
        --
        RETURN OLD;
    END;
$alert_delete$ LANGUAGE plpgsql;

CREATE TRIGGER alert_delete AFTER DELETE ON users_alerts FOR EACH ROW EXECUTE PROCEDURE alert_delete();

-- alert update --

CREATE FUNCTION alert_update() RETURNS trigger AS $alert_update$
    DECLARE
        al_sum INTEGER;
    BEGIN

        -- users meta
        SELECT SUM(alerts_count) INTO al_sum FROM users_alerts WHERE user_id = OLD.user_id;
        UPDATE users_meta SET meta_value = al_sum WHERE user_id = OLD.user_id AND meta_key = 'alerts_sum';

        --
        RETURN OLD;
    END;
$alert_update$ LANGUAGE plpgsql;

CREATE TRIGGER alert_update AFTER UPDATE ON users_alerts FOR EACH ROW EXECUTE PROCEDURE alert_update();

-- vol insert --

CREATE FUNCTION vol_insert() RETURNS trigger AS $vol_insert$
    DECLARE
        vol_sum INTEGER;
    BEGIN

        -- users meta
        SELECT SUM(alerts_count) INTO al_sum FROM users_alerts WHERE user_id = NEW.user_id;
        IF EXISTS (SELECT id FROM users_meta WHERE user_id = NEW.user_id AND meta_key = 'alerts_sum') THEN
            UPDATE users_meta SET meta_value = al_sum WHERE user_id = NEW.user_id AND meta_key = 'alerts_sum';
        ELSE
            INSERT INTO users_meta (user_id, meta_key, meta_value) VALUES (NEW.user_id, 'alerts_sum', al_sum);
        END IF;

        --
        RETURN NEW;
    END;
$vol_insert$ LANGUAGE plpgsql;

CREATE TRIGGER vol_insert AFTER INSERT ON users_vols FOR EACH ROW EXECUTE PROCEDURE vol_insert();

-- vol delete --

-- vol update --

-- view: users vols --

CREATE OR REPLACE VIEW vw_users_vols AS
    SELECT users.id AS user_id, users_vols.id AS vol_id FROM users
    JOIN users_vols ON users.id = users_vols.user_id
    WHERE users_vols.expire_date >= NOW()
    ORDER BY users_vols.vol_size DESC
    LIMIT 1;

-- privileges --

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO echidna_usr;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO echidna_usr;

-- select all

\pset format wrapped
SELECT * FROM vw_users_relations; SELECT * FROM vw_users_vols;
SELECT * FROM users; SELECT * FROM users_meta; SELECT * FROM users_vols; SELECT * FROM users_roles; SELECT * FROM users_alerts; SELECT * FROM hubs; SELECT * FROM hubs_meta; SELECT * FROM posts; SELECT * FROM posts_meta; SELECT * FROM posts_tags; SELECT * FROM posts_comments; SELECT * FROM uploads; 
