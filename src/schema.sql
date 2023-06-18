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
    type TEXT NOT NULL DEFAULT 'heartbeat',
    details TEXT NOT NULL DEFAULT '',
    domain_id TEXT REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE,
    server_id TEXT REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE pages (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    title TEXT NOT NULL,
    hostname TEXT NOT NULL DEFAULT '',
    style TEXT NOT NULL DEFAULT '',
    locale TEXT NOT NULL DEFAULT 'auto'
);

CREATE TABLE pages_to_domains (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    page_id TEXT REFERENCES pages ON DELETE CASCADE ON UPDATE CASCADE,
    domain_id TEXT REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE UNIQUE INDEX idx_pages_to_domains ON pages_to_domains(page_id, domain_id);

CREATE TABLE pages_to_servers (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    page_id TEXT REFERENCES pages ON DELETE CASCADE ON UPDATE CASCADE,
    server_id TEXT REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE UNIQUE INDEX idx_pages_to_servers ON pages_to_servers(page_id, server_id);

CREATE TABLE announcements (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    planned_at TIMESTAMPTZ NOT NULL,
    type TEXT NOT NULL,
    status TEXT NOT NULL,
    page_id TEXT NOT NULL REFERENCES pages ON DELETE CASCADE ON UPDATE CASCADE,

    title TEXT NOT NULL,
    content TEXT NOT NULL
);

CREATE TABLE jobs (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    perform_at TIMESTAMPTZ NOT NULL,
    name TEXT NOT NULL DEFAULT '',
    args JSON NOT NULL DEFAULT '{}',
    frequency TEXT NOT NULL DEFAULT '',
    queue TEXT NOT NULL DEFAULT 'default',
    locked_at TIMESTAMPTZ,
    number_attempts BIGINT NOT NULL DEFAULT 0,
    last_error TEXT NOT NULL DEFAULT '',
    failed_at TIMESTAMPTZ
);
