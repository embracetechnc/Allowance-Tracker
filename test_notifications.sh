#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color
BLUE='\033[0;34m'

# Base URL
API_URL="http://localhost:8000/api"
TOKEN=""

echo -e "${BLUE}Testing Task Notifications${NC}\n"

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

# Test 2: Check Hannah's notifications (should be empty or show unread)
echo -e "\n${BLUE}Test 2: Check Hannah's notifications${NC}"
response=$(call_api "GET" "/notifications")
if [[ $response == *"notifications"* ]]; then
    echo -e "${GREEN}✓ Notifications retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve notifications${NC}"
    echo $response
fi

# Test 3: Mark room cleaning as completed (should generate notification)
echo -e "\n${BLUE}Test 3: Mark room cleaning as completed${NC}"
response=$(call_api "POST" "/tasks/complete" '{"task_type":"room_cleaning"}')
if [[ $response == *"success"* || $response == *"completed"* ]]; then
    echo -e "${GREEN}✓ Task marked as completed${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to mark task as completed${NC}"
    echo $response
fi

# Test 4: Login as William (parent)
echo -e "\n${BLUE}Test 4: Login as William${NC}"
response=$(call_api "POST" "/auth/login" '{"email":"william_stokes@hotmail.com","password":"password123"}')
if [[ $response == *"token"* ]]; then
    echo -e "${GREEN}✓ Parent login successful${NC}"
    TOKEN=$(echo $response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Parent login failed${NC}"
    echo $response
fi

# Test 5: Check William's notifications (should show task completion)
echo -e "\n${BLUE}Test 5: Check William's notifications${NC}"
response=$(call_api "GET" "/notifications")
if [[ $response == *"notifications"* ]]; then
    echo -e "${GREEN}✓ Notifications retrieved successfully${NC}"
    # Store notification ID for marking as read
    NOTIFICATION_ID=$(echo $response | python3 -c "import sys, json; print(json.load(sys.stdin)['notifications'][0]['id'])")
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve notifications${NC}"
    echo $response
fi

# Test 6: Mark notification as read
echo -e "\n${BLUE}Test 6: Mark notification as read${NC}"
response=$(call_api "POST" "/notifications/read" "{\"notification_id\":$NOTIFICATION_ID}")
if [[ $response == *"success"* || $response == *"read"* ]]; then
    echo -e "${GREEN}✓ Notification marked as read${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to mark notification as read${NC}"
    echo $response
fi

# Test 7: Verify task (should generate notification for Hannah)
echo -e "\n${BLUE}Test 7: Verify task${NC}"
response=$(call_api "GET" "/tasks/pending")
TASK_ID=$(echo $response | python3 -c "import sys, json; print(json.load(sys.stdin)['tasks'][0]['id'])")
response=$(call_api "POST" "/tasks/verify" "{\"task_id\":$TASK_ID,\"approved\":true}")
if [[ $response == *"success"* || $response == *"updated"* ]]; then
    echo -e "${GREEN}✓ Task verified successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to verify task${NC}"
    echo $response
fi

# Test 8: Login back as Hannah
echo -e "\n${BLUE}Test 8: Login back as Hannah${NC}"
response=$(call_api "POST" "/auth/login" '{"email":"hannahastokes@icloud.com","password":"password123"}')
if [[ $response == *"token"* ]]; then
    echo -e "${GREEN}✓ Login successful${NC}"
    TOKEN=$(echo $response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Login failed${NC}"
    echo $response
fi

# Test 9: Check Hannah's notifications (should show verification)
echo -e "\n${BLUE}Test 9: Check Hannah's notifications${NC}"
response=$(call_api "GET" "/notifications")
if [[ $response == *"notifications"* ]]; then
    echo -e "${GREEN}✓ Notifications retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve notifications${NC}"
    echo $response
fi

echo -e "\n${BLUE}Notification Tests Complete${NC}"