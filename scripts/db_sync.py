#!/usr/bin/env python3
"""
LLM Price Tracker - Database Sync Script
Fetches data from OpenRouter and syncs changes to a local MariaDB database.
Only writes to the DB if a model is new, its price/specs changed, or it was deleted.
"""

import json
import os
import requests
import mysql.connector
from datetime import datetime, timezone
from dotenv import load_dotenv

# Load environment variables (DB credentials)
load_dotenv(os.path.join(os.path.dirname(__file__), '../.env'))

OPENROUTER_API_URL = "https://openrouter.ai/api/v1/models"

def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASS"),
        database=os.getenv("DB_NAME"),
        port=int(os.getenv("DB_PORT", 3306))
    )

def fetch_models():
    """Fetch all models from OpenRouter API."""
    resp = requests.get(OPENROUTER_API_URL, timeout=15)
    resp.raise_for_status()
    return resp.json().get("data", [])

def sync_to_db(api_models):
    """Compare API data with DB and record only changes."""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    current_ids = set()
    now = datetime.now(timezone.utc)

    for model in api_models:
        or_id = model['id']
        current_ids.add(or_id)
        
        # Extract detailed specs
        architecture = model.get('architecture', {})
        top_provider = model.get('top_provider', {})
        
        context_length = model.get('context_length', 0)
        max_tokens = model.get('top_completion_tokens', 0) or top_provider.get('max_completion_tokens', 0)
        modality = architecture.get('modality', '')
        input_modalities = json.dumps(architecture.get('input_modalities', []))
        output_modalities = json.dumps(architecture.get('output_modalities', []))
        
        # Extract provider from ID (e.g. openai/gpt-4 -> openai)
        provider_name = or_id.split('/')[0] if '/' in or_id else 'unknown'
        
        # Quantization and specifics
        quantization = architecture.get('quantization', '')
        tp_max_tokens = top_provider.get('max_completion_tokens', 0)
        
        # Check if model supports tools/function calling
        supported_params = model.get('supported_parameters', [])
        supports_tools = 'tools' in supported_params or 'tool_choice' in supported_params

        # Extract links
        links = model.get('links', {})
        hf_id = model.get('hugging_face_id')
        if hf_id and 'huggingface' not in links:
            links['huggingface'] = f"https://huggingface.co/{hf_id}"
        
        name = model.get('name', or_id)
        description = model.get('description', '')
        knowledge_cutoff = model.get('knowledge_cutoff')
        created_timestamp = model.get('created')
        expiration_timestamp = model.get('expiration_date')
        
        # Conversion des timestamps en dates lisibles (gère int et str)
        def ts_to_date(ts):
            if not ts: return None
            try:
                return datetime.fromtimestamp(int(ts), tz=timezone.utc)
            except: return None

        created_date = ts_to_date(created_timestamp)
        expiration_date = ts_to_date(expiration_timestamp)
        
        tokenizer = architecture.get('tokenizer', '')
        is_moderated = top_provider.get('is_moderated', False)
        
        pricing = model.get('pricing', {})
        input_price = float(pricing.get('prompt', 0)) * 1_000_000
        output_price = float(pricing.get('completion', 0)) * 1_000_000
        
        # Skip models with negative/placeholder pricing
        if input_price < 0 or output_price < 0:
            continue

        # 1. Ensure model exists in registry
        cursor.execute("SELECT id FROM models WHERE openrouter_id = %s", (or_id,))
        model_record = cursor.fetchone()

        if not model_record:
            cursor.execute(
                "INSERT INTO models (openrouter_id, name, description, status, specs, links, context_length, max_tokens, modality, "
                "input_modalities, output_modalities, provider_name, quantization, tokenizer, top_provider_max_completion_tokens, "
                "supports_tools, is_moderated, knowledge_cutoff, expiration_date, created_at_date, created_at, updated_at) "
                "VALUES (%s, %s, %s, 'active', %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                (or_id, name, description, json.dumps(model), json.dumps(links), context_length, max_tokens, modality, 
                 input_modalities, output_modalities, provider_name, quantization, tokenizer, tp_max_tokens, 
                 supports_tools, is_moderated, knowledge_cutoff, expiration_date, created_date, now, now)
            )
            model_id = cursor.lastrowid
            change_type = 'created'
        else:
            model_id = model_record['id']
            cursor.execute(
                "UPDATE models SET updated_at = %s, name = %s, description = %s, status = 'active', specs = %s, links = %s, "
                "context_length = %s, max_tokens = %s, modality = %s, input_modalities = %s, output_modalities = %s, "
                "provider_name = %s, quantization = %s, tokenizer = %s, top_provider_max_completion_tokens = %s, "
                "supports_tools = %s, is_moderated = %s, knowledge_cutoff = %s, expiration_date = %s, created_at_date = %s WHERE id = %s",
                (now, name, description, json.dumps(model), json.dumps(links), context_length, max_tokens, modality, 
                 input_modalities, output_modalities, provider_name, quantization, tokenizer, tp_max_tokens, 
                 supports_tools, is_moderated, knowledge_cutoff, expiration_date, created_date, model_id)
            )
            
            # 2. Check for price/spec changes
            cursor.execute(
                "SELECT input_price_per_m, output_price_per_m, context_length FROM model_prices_history WHERE model_id = %s ORDER BY id DESC LIMIT 1",
                (model_id,)
            )
            last_price = cursor.fetchone()

            change_type = None
            if not last_price:
                change_type = 'created' # First time tracking price for this model
            elif (abs(float(last_price['input_price_per_m']) - input_price) > 0.0001 or 
                  abs(float(last_price['output_price_per_m']) - output_price) > 0.0001 or
                  int(last_price['context_length']) != context_length):
                change_type = 'price_update'

        # 3. Record history if there's a change
        if change_type:
            cursor.execute(
                "INSERT INTO model_prices_history (model_id, timestamp, input_price_per_m, output_price_per_m, context_length, change_type) VALUES (%s, %s, %s, %s, %s, %s)",
                (model_id, now, input_price, output_price, context_length, change_type)
            )
            print(f"[+] {change_type.upper()}: {or_id}")

    # 4. Handle deletions (models in DB but not in API)
    cursor.execute("SELECT id, openrouter_id FROM models WHERE status = 'active'")
    db_models = cursor.fetchall()
    for db_model in db_models:
        if db_model['openrouter_id'] not in current_ids:
            cursor.execute("UPDATE models SET status = 'deleted' WHERE id = %s", (db_model['id'],))
            cursor.execute(
                "INSERT INTO model_prices_history (model_id, timestamp, change_type) VALUES (%s, %s, 'deleted')",
                (db_model['id'], now)
            )
            print(f"[-] DELETED: {db_model['openrouter_id']}")

    conn.commit()
    cursor.close()
    conn.close()
    print("Sync completed.")

if __name__ == "__main__":
    try:
        models = fetch_models()
        sync_to_db(models)
    except Exception as e:
        print(f"Error: {e}")
