CREATE TABLE IF NOT EXISTS events (
  id         UUID PRIMARY KEY,
  type       TEXT NOT NULL,
  payload    JSONB NOT NULL,
  status     TEXT NOT NULL DEFAULT 'queued',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  processed_at TIMESTAMPTZ
);
