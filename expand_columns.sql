USE llm_tracker;

ALTER TABLE models 
ADD COLUMN context_length INT DEFAULT 0,
ADD COLUMN max_tokens INT DEFAULT 0,
ADD COLUMN modality VARCHAR(100) DEFAULT '',
ADD COLUMN input_modalities JSON,
ADD COLUMN output_modalities JSON,
ADD COLUMN provider_name VARCHAR(100) DEFAULT '',
ADD COLUMN quantization VARCHAR(50) DEFAULT '',
ADD COLUMN top_provider_max_completion_tokens INT DEFAULT 0;
