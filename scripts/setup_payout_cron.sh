#!/bin/bash

# Make the payout script executable
chmod +x process_payouts.php

# Create a temporary crontab file
TEMP_CRON=$(mktemp)

# Add the cron job to run daily at 9:00 AM
echo "0 9 * * * php $(pwd)/process_payouts.php >> $(pwd)/logs/payouts.log 2>&1" > $TEMP_CRON

# Create logs directory if it doesn't exist
mkdir -p logs

# Install the new cron job
crontab $TEMP_CRON

# Remove the temporary file
rm $TEMP_CRON

echo "Cron job installed successfully!"
echo "Payouts will be processed daily at 9:00 AM"
echo "Logs will be written to: $(pwd)/logs/payouts.log"
