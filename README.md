# Family Allowance Tracker

A modern web application for tracking children's allowances based on completed tasks and behavior.

## Features

- Task management system with parental verification
- Bi-weekly allowance calculations
- Automatic deductions based on school performance
- Daily Bible verse integration
- Cash App integration for payments
- Email notifications for task completion
- Progress tracking and reporting

## Tech Stack

- Frontend: Vue.js, TailwindCSS, Chart.js
- Backend: PHP 8.1
- Database: MySQL
- Authentication: JWT
- Email: PHPMailer
- Payment: Cash App API

## Setup Instructions

1. Clone the repository
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install Node.js dependencies:
   ```bash
   npm install
   ```
4. Copy `.env.example` to `.env` and configure:
   - Database credentials
   - Email settings
   - Cash App API keys
   - Bible.com API key

5. Start development server:
   ```bash
   npm run dev
   ```
   
6. Start PHP server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Environment Variables

Create a `.env` file with the following variables:

```env
DB_HOST=localhost
DB_NAME=allowance_tracker
DB_USER=root
DB_PASS=

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=

CASHAPP_CLIENT_ID=
CASHAPP_CLIENT_SECRET=

BIBLE_API_KEY=
```

## Project Structure

```
├── src/
│   ├── api/          # PHP backend files
│   ├── components/   # Vue components
│   ├── store/        # Vuex store modules
│   ├── views/        # Vue page components
│   └── router/       # Vue router configuration
├── public/           # Static assets
├── database/         # Database migrations and seeds
└── tests/           # Test files
```
