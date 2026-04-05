#!/usr/bin/env python3
"""
LLM Price Alert Bot - Surveille les baisses de prix significatives
et envoie des notifications Discord.

Usage: python scripts/price_alerts.py [--threshold 10] [--dry-run]

Options:
  --threshold X  Seuil de baisse en % (défaut: 10%)
  --dry-run      Afficher les alertes sans envoyer
"""

import sys
import json
import os
import requests
import mysql.connector
from datetime import datetime, timedelta
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), '../.env'))

def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASS"),
        database=os.getenv("DB_NAME"),
        port=int(os.getenv("DB_PORT", 3306))
    )

def find_price_drops(threshold_pct=10.0):
    """Trouve les modèles dont le prix a baissé de plus de threshold_pct%."""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    # Comparer le prix actuel avec le prix d'il y a 7 jours
    query = """
    SELECT 
        m.id,
        m.name,
        m.openrouter_id,
        m.provider_name,
        m.supports_tools,
        curr.input_price_per_m as current_input,
        curr.output_price_per_m as current_output,
        prev.input_price_per_m as prev_input,
        prev.output_price_per_m as prev_output,
        curr.timestamp as current_ts,
        prev.timestamp as prev_ts
    FROM models m
    INNER JOIN model_prices_history curr ON m.id = curr.model_id
    INNER JOIN model_prices_history prev ON m.id = prev.model_id
    WHERE curr.timestamp >= NOW() - INTERVAL 1 DAY
      AND prev.timestamp BETWEEN NOW() - INTERVAL 8 DAY AND NOW() - INTERVAL 6 DAY
      AND curr.input_price_per_m > 0
      AND prev.input_price_per_m > 0
    """
    
    cursor.execute(query)
    rows = cursor.fetchall()
    cursor.close()
    conn.close()
    
    alerts = []
    for row in rows:
        input_drop = ((row['prev_input'] - row['current_input']) / row['prev_input']) * 100
        output_drop = ((row['prev_output'] - row['current_output']) / row['prev_output']) * 100
        
        if input_drop >= threshold_pct or output_drop >= threshold_pct:
            alerts.append({
                'model': row['name'],
                'id': row['openrouter_id'],
                'provider': row['provider_name'],
                'supports_tools': row['supports_tools'],
                'input_drop_pct': round(input_drop, 2),
                'output_drop_pct': round(output_drop, 2),
                'current_input': row['current_input'],
                'prev_input': row['prev_input'],
                'current_output': row['current_output'],
                'prev_output': row['prev_output'],
            })
    
    # Trier par plus grosse baisse
    alerts.sort(key=lambda x: max(x['input_drop_pct'], x['output_drop_pct']), reverse=True)
    return alerts

def send_discord_alerts(alerts, webhook_url, dry_run=False):
    """Envoie les alertes sur Discord."""
    if not alerts:
        print("✅ Aucune alerte de baisse de prix significative.")
        return
    
    print(f"🚨 {len(alerts)} baisse(s) de prix détectée(s)!\n")
    
    # Créer un embed Discord
    embeds = []
    for alert in alerts[:10]:  # Max 10 pour éviter le spam
        drop_emoji = "📉"
        tools_badge = " 🛠️" if alert['supports_tools'] else ""
        
        description = f"**Provider:** {alert['provider']}{tools_badge}\n"
        description += f"**Input:** `${alert['prev_input']:.4f}` → `${alert['current_input']:.4f}` (**-{alert['input_drop_pct']}%**)\n"
        description += f"**Output:** `${alert['prev_output']:.4f}` → `${alert['current_output']:.4f}` (**-{alert['output_drop_pct']}%**)"
        
        embed = {
            "title": f"{drop_emoji} {alert['model']}",
            "description": description,
            "url": f"https://openrouter.ai/models/{alert['id']}",
            "color": 5814783,  # Vert
            "footer": {
                "text": "LLM Price Tracker - Kyra ⌬"
            }
        }
        embeds.append(embed)
        
        # Afficher dans la console
        print(f"  {drop_emoji} {alert['model']} ({alert['id']})")
        print(f"     Input: ${alert['prev_input']:.4f} → ${alert['current_input']:.4f} (-{alert['input_drop_pct']}%)")
        print(f"     Output: ${alert['prev_output']:.4f} → ${alert['current_output']:.4f} (-{alert['output_drop_pct']}%)\n")
    
    if dry_run:
        print(f"\n[DRY RUN] {len(embeds)} notification(s) Discord envoyée(s).")
        return
    
    # Envoyer par batches de 10 embeds max (limite Discord)
    for i in range(0, len(embeds), 10):
        batch = embeds[i:i+10]
        payload = {
            "content": f"🚨 **{len(alerts)} baisse(s) de prix LLM détectée(s)!**",
            "embeds": batch
        }
        
        try:
            resp = requests.post(webhook_url, json=payload, timeout=10)
            if resp.status_code == 204:
                print(f"✅ Batch {i//10 + 1} envoyé avec succès.")
            else:
                print(f"❌ Erreur Discord: {resp.status_code} - {resp.text}")
        except Exception as e:
            print(f"❌ Erreur envoi Discord: {e}")

def main():
    # Parser les arguments
    threshold = 10.0
    dry_run = False
    
    args = sys.argv[1:]
    if '--threshold' in args:
        idx = args.index('--threshold')
        if idx + 1 < len(args):
            threshold = float(args[idx + 1])
    
    if '--dry-run' in args:
        dry_run = True
    
    print(f"🔍 Recherche de baisses de prix >= {threshold}% (7 jours)...\n")
    
    # Trouver les baisses
    alerts = find_price_drops(threshold)
    
    # Envoyer les alertes
    webhook_url = os.getenv("DISCORD_WEBHOOK_URL")
    if webhook_url and not dry_run:
        send_discord_alerts(alerts, webhook_url, dry_run)
    else:
        send_discord_alerts(alerts, "", dry_run)
        if not webhook_url:
            print("\n⚠️  DISCORD_WEBHOOK_URL non configuré dans .env")

if __name__ == "__main__":
    main()
