CREATE TABLE users (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    email TEXT,
    free_mobile_login TEXT,
    free_mobile_key TEXT
);

CREATE TABLE domains (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL
);

CREATE TABLE servers (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    hostname TEXT NOT NULL,
    ipv4 TEXT NOT NULL,
    ipv6 TEXT,
    auth_token TEXT NOT NULL
);

CREATE TABLE heartbeats (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    is_success BOOLEAN NOT NULL,
    details TEXT NOT NULL DEFAULT '',
    domain_id TEXT NOT NULL REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE metrics (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    payload JSON NOT NULL,
    server_id TEXT NOT NULL REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE alarms (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    notified_at TIMESTAMPTZ,
    finished_at TIMESTAMPTZ,
    details TEXT NOT NULL DEFAULT '',
    domain_id TEXT NOT NULL REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
);
