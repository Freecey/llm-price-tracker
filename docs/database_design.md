# LLM Price Tracker - Database Design & Strategy

## Objective
Track price evolution, availability, and specification changes for OpenRouter models without storing redundant static data.

## Storage Strategy: "Changes Only" (Delta Approach)

To minimize database size and maximize historical value, we will not store the full model list every time. Instead, we store:
1.  **State Checkpoints:** A lightweight "state" record for each model at a given time.
2.  **Delta Events:** Specific records for when a price or spec changes.

### Proposed Tables (MariaDB)

#### 1. `models` (The Registry)
Stores the current known state of every model.
*   `id`: Primary Key (auto-increment)
*   `openrouter_id`: Varchar (Unique, e.g., `openrouter/google/gemma-3-27b-it`)
*   `name`: Varchar
*   `status`: Enum ('active', 'deleted', 're-added')
*   `last_seen`: Timestamp
*   `first_seen`: Timestamp

#### 2. `model_prices_history` (The Timeline)
Stores only the changes. If a price doesn't change, no new row is added.
*   `id`: Primary Key
*   `model_id`: Foreign Key -> `models.id`
*   `timestamp`: Timestamp
*   `input_price_per_m`: Decimal(10, 4)
*   `output_price_per_m`: Decimal(10, 4)
*   `context_length`: Int
*   `change_type`: Enum ('created', 'price_update', 'spec_update', 'deleted')

### Logic Flow for the Script
1.  **Fetch:** Get the live list from OpenRouter API.
2.  **Compare:** 
    *   If `openrouter_id` is new -> Insert into `models` and add a 'created' event in `history`.
    *   If `openrouter_id` exists -> Compare current prices/specs with the last entry in `history`.
    *   If different -> Insert a new row in `history` with 'price_update' or 'spec_update'.
    *   If a model in the DB is missing from the API -> Mark as 'deleted' in `models` and add a 'deleted' event.

## Tech Stack Proposal
*   **Backend:** Laravel (Lightweight, excellent Eloquent ORM for this "delta" logic).
*   **Database:** MariaDB (Already available on your ISPConfig/Proxmox stack).
*   **Frontend:** Bootstrap 5 + Chart.js (for price evolution graphs).
*   **Automation:** Cron job running the Python script every 6-12 hours.

## Next Steps
1.  Define the Laravel Migration files.
2.  Update the Python script to handle DB connections and delta-checking.
3.  Build the "Model Detail" view in Laravel to show the price evolution graph.
