#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color
BLUE='\033[0;34m'

# Base URL
API_URL="http://localhost:8000/api"
TOKEN=""

echo -e "${BLUE}Testing Task Verification Workflow${NC}\n"

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

# Test 2: Mark room cleaning as completed
echo -e "\n${BLUE}Test 2: Mark room cleaning as completed${NC}"
response=$(call_api "POST" "/tasks/complete" '{"task_type":"room_cleaning"}')
if [[ $response == *"success"* || $response == *"completed"* ]]; then
    echo -e "${GREEN}✓ Task marked as completed${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to mark task as completed${NC}"
    echo $response
fi

# Test 3: Mark bathroom cleaning as completed
echo -e "\n${BLUE}Test 3: Mark bathroom cleaning as completed${NC}"
response=$(call_api "POST" "/tasks/complete" '{"task_type":"bathroom_cleaning"}')
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

# Test 5: Get pending verifications
echo -e "\n${BLUE}Test 5: Get pending verifications${NC}"
response=$(call_api "GET" "/tasks/pending")
if [[ $response == *"tasks"* ]]; then
    echo -e "${GREEN}✓ Pending tasks retrieved successfully${NC}"
    # Store the first task ID for verification
    TASK_ID=$(echo $response | python3 -c "import sys, json; print(json.load(sys.stdin)['tasks'][0]['id'])")
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve pending tasks${NC}"
    echo $response
fi

# Test 6: Approve the first task
echo -e "\n${BLUE}Test 6: Approve room cleaning task${NC}"
response=$(call_api "POST" "/tasks/verify" "{\"task_id\":$TASK_ID,\"approved\":true}")
if [[ $response == *"updated"* ]]; then
    echo -e "${GREEN}✓ Task approved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to approve task${NC}"
    echo $response
fi

# Test 7: Get updated pending verifications
echo -e "\n${BLUE}Test 7: Get updated pending verifications${NC}"
response=$(call_api "GET" "/tasks/pending")
if [[ $response == *"tasks"* ]]; then
    echo -e "${GREEN}✓ Updated pending tasks retrieved successfully${NC}"
    # Store the next task ID for rejection
    TASK_ID=$(echo $response | python3 -c "import sys, json; print(json.load(sys.stdin)['tasks'][0]['id'])")
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve updated pending tasks${NC}"
    echo $response
fi

# Test 8: Reject the second task
echo -e "\n${BLUE}Test 8: Reject bathroom cleaning task${NC}"
response=$(call_api "POST" "/tasks/verify" "{\"task_id\":$TASK_ID,\"approved\":false}")
if [[ $response == *"updated"* ]]; then
    echo -e "${GREEN}✓ Task rejected successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to reject task${NC}"
    echo $response
fi

# Test 9: Login back as Hannah
echo -e "\n${BLUE}Test 9: Login back as Hannah${NC}"
response=$(call_api "POST" "/auth/login" '{"email":"hannahastokes@icloud.com","password":"password123"}')
if [[ $response == *"token"* ]]; then
    echo -e "${GREEN}✓ Login successful${NC}"
    TOKEN=$(echo $response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Login failed${NC}"
    echo $response
fi

# Test 10: Check Hannah's tasks after verification
echo -e "\n${BLUE}Test 10: Check tasks after verification${NC}"
response=$(call_api "GET" "/tasks")
if [[ $response == *"tasks"* ]]; then
    echo -e "${GREEN}✓ Tasks retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve tasks${NC}"
    echo $response
fi

echo -e "\n${BLUE}Task Verification Tests Complete${NC}"