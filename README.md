# Family Allowance Tracker

A modern web application for managing children's tasks, allowances, and rewards.

## Features

- Task Management

  - Create and assign tasks
  - Track task completion
  - Upload completion photos
  - Task checklists
  - Time tracking

- Allowance System

  - Weekly allowance rates
  - Points-based rewards
  - Task completion bonuses
  - Payment tracking

- Parent Controls

  - Task categories
  - Minimum requirements
  - Task instructions
  - Video tutorials

- Child Features
  - Task progress tracking
  - Achievement system
  - Reward redemption
  - Activity history

## Technology Stack

- Frontend: Vue.js 3 with Composition API
- State Management: Pinia
- UI Framework: Tailwind CSS
- Icons: Heroicons
- Charts: Chart.js
- Testing: Vitest + Cypress

## Coverage

![statements coverage](https://img.shields.io/badge/statements-85%-brightgreen)
![branches coverage](https://img.shields.io/badge/branches-84%-brightgreen)
![functions coverage](https://img.shields.io/badge/functions-82%-brightgreen)
![lines coverage](https://img.shields.io/badge/lines-82%-brightgreen)
## Development

### Prerequisites

- Node.js 18+
- npm 9+
- PHP 8.1+
- Composer 2+
- MySQL 8+

### Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   npm install
   composer install
   ```
3. Set up environment:
   ```bash
   cp .env.example .env
   ```
4. Start development server:
   ```bash
   npm run dev
   ```

### Testing

Run unit tests:

```bash
npm run test
```

Run test coverage:

```bash
npm run test:coverage
```

Run end-to-end tests:

```bash
npm run test:e2e
```

## License

MIT License
