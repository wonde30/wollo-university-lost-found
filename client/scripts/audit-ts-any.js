const fs = require('fs');
const path = require('path');

const patterns = {
  colonAny: /:\s*any\b/g,
  asAny: /\bas\s+any\b/g,
  arrayAny: /Array<\s*any\s*>/g,
  promiseAny: /Promise<\s*any\s*>/g,
  recordAny: /Record<\s*string\s*,\s*any\s*>/g,
};

const results = {
  colonAny: [],
  asAny: [],
  arrayAny: [],
  promiseAny: [],
  recordAny: [],
};

function walkDir(dir) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      if (file !== 'node_modules' && file !== '.git' && file !== 'dist') {
        walkDir(fullPath);
      }
    } else if (file.endsWith('.ts') || file.endsWith('.vue')) {
      scanFile(fullPath);
    }
  }
}

function scanFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  const lines = content.split('\n');
  const relPath = path.relative(path.resolve(__dirname, '..'), filePath).replace(/\\/g, '/');

  lines.forEach((line, idx) => {
    const lineNum = idx + 1;
    // Skip comments if purely commented
    const trimmed = line.trim();
    if (trimmed.startsWith('//') || trimmed.startsWith('/*') || trimmed.startsWith('*')) {
      return;
    }

    for (const [key, regex] of Object.entries(patterns)) {
      regex.lastIndex = 0;
      if (regex.test(line)) {
        results[key].push({
          file: relPath,
          line: lineNum,
          code: trimmed,
        });
      }
    }
  });
}

const targetDir = path.resolve(__dirname, '../src');
walkDir(targetDir);

console.log('=== TYPESCRIPT "ANY" AUDIT RESULTS ===\n');
let total = 0;
for (const [key, list] of Object.entries(results)) {
  console.log(`- ${key}: ${list.length} occurrences`);
  total += list.length;
}
console.log(`\nTOTAL 'ANY' VARIANTS: ${total}\n`);

console.log(JSON.stringify(results, null, 2));
