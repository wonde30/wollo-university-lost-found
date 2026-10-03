const fs = require('fs');
const path = require('path');

function walk(dir) {
  let files = [];
  for (const item of fs.readdirSync(dir)) {
    const full = path.join(dir, item);
    if (fs.statSync(full).isDirectory()) {
      if (item !== 'node_modules' && item !== '.git' && item !== 'dist') {
        files = files.concat(walk(full));
      }
    } else if (full.endsWith('.ts') || full.endsWith('.vue')) {
      files.push(full);
    }
  }
  return files;
}

const files = walk(path.join(__dirname, 'src'));
console.log('Total files to check:', files.length);

const issues = [];

for (const f of files) {
  const content = fs.readFileSync(f, 'utf8');
  const lines = content.split('\n');
  const rel = path.relative(__dirname, f);

  lines.forEach((line, idx) => {
    const trimmed = line.trim();
    // Check for direct .sort() without [...arr] or .slice()
    if (/\b(?<!\[\.\.\.)([a-zA-Z0-9_]+)\.sort\(/g.test(trimmed)) {
      if (!trimmed.includes('[...') && !trimmed.includes('.slice(') && !trimmed.includes('toSorted') && !trimmed.includes('computed')) {
        // Exclude Array.from or new Array or Object.values
        if (!trimmed.includes('Array.from') && !trimmed.includes('Object.keys') && !trimmed.includes('Object.values') && !trimmed.includes('Object.entries')) {
          issues.push({ file: rel, line: idx + 1, code: trimmed, type: 'potential in-place sort' });
        }
      }
    }

    // Check for direct .reverse() without copying
    if (/\b(?<!\[\.\.\.)([a-zA-Z0-9_]+)\.reverse\(/g.test(trimmed)) {
      if (!trimmed.includes('[...') && !trimmed.includes('.slice(') && !trimmed.includes('toReversed')) {
        if (!trimmed.includes('Array.from') && !trimmed.includes('.split(')) {
          issues.push({ file: rel, line: idx + 1, code: trimmed, type: 'potential in-place reverse' });
        }
      }
    }
  });
}

console.log(`Found ${issues.length} potential mutative array operations:`);
console.log(JSON.stringify(issues, null, 2));
