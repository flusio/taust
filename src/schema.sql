CREATE TABLE users (
    id TEXT PRIMARY KEY,
    created_at TIMESTAMPTZ NOT NULL,
    username TEXT NOT NULL,
    password_hash TEXT NOT NULL
);
