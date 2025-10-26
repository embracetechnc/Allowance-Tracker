#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color
BLUE='\033[0;34m'

# Base URL
API_URL="http://localhost:8000/api"
TOKEN=""

echo -e "${BLUE}Testing Cash App Authentication${NC}\n"

# Function to make API calls
call_api() {
    local method=$1
    local endpoint=$2
    local data=$3
    local auth_header=""
    
    if [ ! -z "$TOKEN" ]; then
        auth_header="-H 'Authorization: Bearer $TOKEN'"
    fi
    
    if [ ! -z "$data" ]; then
        eval "curl -s -X $method \"$API_URL$endpoint\" \
            -H 'Content-Type: application/json' \
            $auth_header \
            -d '$data'"
    else
        eval "curl -s -X $method \"$API_URL$endpoint\" \
            -H 'Content-Type: application/json' \
            $auth_header"
    fi
}

# Test 1: Login as Hannah
echo -e "\n${BLUE}Test 1: Login as Hannah${NC}"
response=$(call_api "POST" "/auth/login" '{"email":"hannahastokes@icloud.com","password":"password123"}')
if [[ $response == *"token"* ]]; then
    echo -e "${GREEN}✓ Login successful${NC}"
    TOKEN=$(echo $response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Login failed${NC}"
    echo $response
fi

# Test 2: Get Cash App Auth URL
echo -e "\n${BLUE}Test 2: Get Cash App Auth URL${NC}"
response=$(call_api "GET" "/cashapp/auth")
if [[ $response == *"auth_url"* ]]; then
    echo -e "${GREEN}✓ Auth URL retrieved successfully${NC}"
    AUTH_URL=$(echo $response | grep -o '"auth_url":"[^"]*' | cut -d'"' -f4)
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to get auth URL${NC}"
    echo $response
fi

# Test 3: Simulate OAuth Callback (with mock code)
echo -e "\n${BLUE}Test 3: Simulate OAuth Callback${NC}"
response=$(call_api "POST" "/cashapp/callback" '{"code":"mock_auth_code","state":"mock_state"}')
if [[ $response == *"error"* ]]; then
    echo -e "${RED}✗ Callback failed (expected in test environment)${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${GREEN}✓ Callback processed successfully${NC}"
    echo $response | python3 -m json.tool
fi

# Test 4: Refresh Token
echo -e "\n${BLUE}Test 4: Refresh Token${NC}"
response=$(call_api "POST" "/cashapp/refresh")
if [[ $response == *"error"* ]]; then
    echo -e "${RED}✗ Token refresh failed (expected without valid token)${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${GREEN}✓ Token refreshed successfully${NC}"
    echo $response | python3 -m json.tool
fi

# Test 5: Unlink Account
echo -e "\n${BLUE}Test 5: Unlink Account${NC}"
response=$(call_api "POST" "/cashapp/unlink")
if [[ $response == *"success"* ]]; then
    echo -e "${GREEN}✓ Account unlinked successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to unlink account${NC}"
    echo $response | python3 -m json.tool
fi

echo -e "\n${BLUE}Cash App Authentication Tests Complete${NC}"
