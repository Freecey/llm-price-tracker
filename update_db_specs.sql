USE llm_tracker;

ALTER TABLE models ADD COLUMN specs JSON AFTER status;
