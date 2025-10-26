import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Read coverage summary
const coverageSummaryPath = path.join(__dirname, '../coverage/unit/coverage-summary.json');
const coverageData = JSON.parse(fs.readFileSync(coverageSummaryPath, 'utf8'));
const total = coverageData.total;

// Generate badge data
const badges = {
  statements: {
    label: 'statements',
    message: `${total.statements.pct}%`,
    color: total.statements.pct >= 80 ? 'brightgreen' : 'red'
  },
  branches: {
    label: 'branches',
    message: `${total.branches.pct}%`,
    color: total.branches.pct >= 80 ? 'brightgreen' : 'red'
  },
  functions: {
    label: 'functions',
    message: `${total.functions.pct}%`,
    color: total.functions.pct >= 80 ? 'brightgreen' : 'red'
  },
  lines: {
    label: 'lines',
    message: `${total.lines.pct}%`,
    color: total.lines.pct >= 80 ? 'brightgreen' : 'red'
  }
};

// Generate badge markdown
const badgeMarkdown = Object.entries(badges)
  .map(([key, data]) => {
    const badgeUrl = `https://img.shields.io/badge/${data.label}-${data.message}-${data.color}`;
    return `![${key} coverage](${badgeUrl})`;
  })
  .join('\n');

// Update README.md
const readmePath = path.join(__dirname, '../README.md');
let readme = fs.readFileSync(readmePath, 'utf8');

// Replace or add coverage badges section
const badgesSection = '## Coverage\n\n' + badgeMarkdown + '\n';
if (readme.includes('## Coverage')) {
  readme = readme.replace(/## Coverage\n[\s\S]*?(?=##|$)/, badgesSection);
} else {
  readme += '\n' + badgesSection;
}

fs.writeFileSync(readmePath, readme);

