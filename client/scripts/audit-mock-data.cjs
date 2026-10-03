const fs = require('fs');
const path = require('path');

const terms = [
  'mock',
  'fake',
  'demo',
  'sample',
  'fixture',
  'placeholder',
  'unsplash',
];

const results = {};
terms.forEach(t => results[t] = []);

function walkDir(dir) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      if (file !== 'node_modules' && file !== '.git' && file !== 'dist' && file !== 'coverage') {
        walkDir(fullPath);
      }
    } else if (file.endsWith('.ts') || file.endsWith('.vue') || file.endsWith('.json')) {
      scanFile(fullPath);
    }
  }
}

function scanFile(filePath) {
  // Ignore tests, specs, and this audit script itself
  if (filePath.includes('.spec.') || filePath.includes('.test.') || filePath.includes('audit-') || filePath.includes('mockServiceWorker')) {
    return;
  }
  const content = fs.readFileSync(filePath, 'utf8');
  const lines = content.split('\n');
  const relPath = path.relative(path.resolve(__dirname, '..'), filePath).replace(/\\/g, '/');

  lines.forEach((line, idx) => {
    const lineNum = idx + 1;
    const lower = line.toLowerCase();
    
    // Ignore input HTML placeholder attributes like placeholder="Enter email"
    const cleanedLine = lower.replace(/placeholder\s*=\s*"[^"]*"/g, '').replace(/placeholder\s*=\s*'[^']*'/g, '').replace(/:placeholder\s*=\s*"[^"]*"/g, '');

    terms.forEach(term => {
      if (term === 'placeholder') {
        // Only check if placeholder is used in variable names or data structures, not standard HTML placeholder attr
        if (/\bplaceholder(?!s*=)\b/i.test(cleanedLine)) {
          results[term].push({ file: relPath, line: lineNum, text: line.trim() });
        }
      } else {
        const regex = new RegExp(`\\b${term}\\b`, 'i');
        if (regex.test(lower)) {
          results[term].push({ file: relPath, line: lineNum, text: line.trim() });
        }
      }
    });
  });
}

walkDir(path.resolve(__dirname, '../src'));

console.log('=== MOCK / FAKE / SAMPLE DATA AUDIT RESULTS ===\n');
let totalMatches = 0;
for (const [term, matches] of Object.entries(results)) {
  console.log(`- "${term}": ${matches.length} matches`);
  totalMatches += matches.length;
}
console.log(`\nTOTAL: ${totalMatches}\n`);

for (const [term, matches] of Object.entries(results)) {
  if (matches.length > 0) {
    console.log(`\n--- Matches for "${term}" (${matches.length}) ---`);
    matches.forEach(m => console.log(`  ${m.file}:${m.line} -> ${m.text}`));
  }
}
