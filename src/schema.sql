CREATE TABLE users (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL
);

CREATE TABLE domains (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL
);

CREATE TABLE heartbeats (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    is_success BOOLEAN NOT NULL,
    details TEXT NOT NULL DEFAULT '',
    domain_id TEXT NOT NULL REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
);
