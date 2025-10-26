#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color
BLUE='\033[0;34m'

# Base URL
API_URL="http://localhost:8000/api"

echo -e "${BLUE}Testing Bible API Endpoints${NC}\n"

# Test 1: Get Daily Verse
echo -e "\n${BLUE}Test 1: Get Daily Verse${NC}"
response=$(curl -s "$API_URL/bible/daily")
if [[ $response == *"verse"* ]]; then
    echo -e "${GREEN}✓ Daily verse retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve daily verse${NC}"
    echo $response
fi

# Test 2: Get Money Verse
echo -e "\n${BLUE}Test 2: Get Money Verse${NC}"
response=$(curl -s "$API_URL/bible/money")
if [[ $response == *"verse"* ]]; then
    echo -e "${GREEN}✓ Money verse retrieved successfully${NC}"
    echo $response | python3 -m json.tool
else
    echo -e "${RED}✗ Failed to retrieve money verse${NC}"
    echo $response
fi

echo -e "\n${BLUE}Bible API Tests Complete${NC}"
