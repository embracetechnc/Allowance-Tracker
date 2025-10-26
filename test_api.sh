#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color
BLUE='\033[0;34m'

# Base URL
API_URL="http://localhost:8000/api"
TOKEN=""

echo -e "${BLUE}Testing Allowance Tracker API${NC}\n"

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

# Test 2: Get Hannah's tasks
echo -e "\n${BLUE}Test 2: Get Hannah's tasks${NC}"
response=$(call_api "GET" "/tasks")
if [[ $response == *"tasks"* ]]; then
    echo -e "${GREEN}✓ Tasks retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve tasks${NC}"
    echo $response
fi

# Test 3: Get Hannah's earnings report
echo -e "\n${BLUE}Test 3: Get Hannah's earnings report${NC}"
response=$(call_api "GET" "/reports/earnings")
if [[ $response == *"monthly_earnings"* ]]; then
    echo -e "${GREEN}✓ Earnings report retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve earnings report${NC}"
    echo $response
fi

# Test 4: Get Hannah's school points
echo -e "\n${BLUE}Test 4: Get Hannah's school points${NC}"
response=$(call_api "GET" "/reports/school-points")
if [[ $response == *"weekly_points"* ]]; then
    echo -e "${GREEN}✓ School points retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve school points${NC}"
    echo $response
fi

# Test 5: Login as William (parent)
echo -e "\n${BLUE}Test 5: Login as William${NC}"
response=$(call_api "POST" "/auth/login" '{"email":"william_stokes@hotmail.com","password":"password123"}')
if [[ $response == *"token"* ]]; then
    echo -e "${GREEN}✓ Parent login successful${NC}"
    TOKEN=$(echo $response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Parent login failed${NC}"
    echo $response
fi

# Test 6: Get pending verifications
echo -e "\n${BLUE}Test 6: Get pending verifications${NC}"
response=$(call_api "GET" "/tasks/pending")
if [[ $response == *"tasks"* ]]; then
    echo -e "${GREEN}✓ Pending tasks retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve pending tasks${NC}"
    echo $response
fi

# Test 7: Get Haven's earnings report (as parent)
echo -e "\n${BLUE}Test 7: Get Haven's earnings report${NC}"
response=$(call_api "GET" "/reports/earnings?user_id=4")
if [[ $response == *"monthly_earnings"* ]]; then
    echo -e "${GREEN}✓ Haven's earnings report retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve Haven's earnings report${NC}"
    echo $response
fi

# Test 8: Get Haven's school points (as parent)
echo -e "\n${BLUE}Test 8: Get Haven's school points${NC}"
response=$(call_api "GET" "/reports/school-points?user_id=4")
if [[ $response == *"weekly_points"* ]]; then
    echo -e "${GREEN}✓ Haven's school points retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve Haven's school points${NC}"
    echo $response
fi

echo -e "\n${BLUE}API Tests Complete${NC}"