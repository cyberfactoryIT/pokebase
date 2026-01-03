#!/bin/bash

# Run ETL pipeline in background with logging
# Usage: ./run-pipeline-background.sh
# Then: tail -f storage/logs/etl-pipeline.log

LOG_FILE="storage/logs/etl-pipeline.log"

echo "🚀 Starting ETL pipeline in background..."
echo "📝 Log file: $LOG_FILE"
echo ""

# Create logs directory if needed
mkdir -p storage/logs

# Run in background, redirect output to log
nohup ./simulate-etl-pipeline.sh > "$LOG_FILE" 2>&1 &

PID=$!
echo "✅ Pipeline started with PID: $PID"
echo ""
echo "Monitor with:"
echo "  tail -f $LOG_FILE"
echo ""
echo "Check status:"
echo "  ps aux | grep simulate-etl"
echo ""
echo "Kill if needed:"
echo "  kill $PID"
