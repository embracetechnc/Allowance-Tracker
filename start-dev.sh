#!/bin/bash

# Export PATH to include Homebrew binaries
export PATH="/opt/homebrew/bin:/opt/homebrew/opt/php@8.1/bin:/opt/homebrew/opt/php@8.1/sbin:$PATH"

# Start PHP server in the background
php -S localhost:8000 -t public &
PHP_PID=$!

# Start Vite dev server
npm run dev &
VITE_PID=$!

# Function to kill both servers
cleanup() {
    echo "Shutting down servers..."
    kill $PHP_PID
    kill $VITE_PID
    exit 0
}

# Set up trap to catch Ctrl+C
trap cleanup INT

# Keep script running
echo "Development servers started:"
echo "PHP server running at http://localhost:8000 (API)"
echo "Vite dev server running at http://localhost:3000 (Frontend)"
echo "Press Ctrl+C to stop both servers"

# Wait forever
wait