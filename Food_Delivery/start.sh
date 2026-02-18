#!/bin/bash

echo "🚀 Avvio UniPr Food Delivery in modalità sviluppo..."

# Costruisce le immagini e avvia i container in background
docker-compose up --build -d

echo "✅ Sistema avviato!"
echo "🌐 Frontend: http://localhost:8000"
echo "⚙️  Backend:  http://localhost:8001"
echo "📊 Database: localhost:3306"

# Segue i log per vedere errori in tempo reale
docker-compose logs -f