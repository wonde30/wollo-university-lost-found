import fs from 'fs';
import path from 'path';

function parseLocale(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  content = content.replace(/^[\s\S]*?export\s+const\s+\w+\s*=\s*\{/, '{');
  content = content.replace(/\};\s*export\s+default[\s\S]*$/, '}');
  return (new Function(`return ${content}`))();
}

function getLeafKeys(obj, prefix = '') {
  let keys = [];
  for (const k of Object.keys(obj)) {
    const nextKey = prefix ? `${prefix}.${k}` : k;
    if (typeof obj[k] === 'object' && obj[k] !== null && !Array.isArray(obj[k])) {
      keys = keys.concat(getLeafKeys(obj[k], nextKey));
    } else {
      keys.push(nextKey);
    }
  }
  return keys;
}

const clientDir = path.resolve('c:/Users/W/Desktop/wollo-lost-found/client');
const enPath = path.join(clientDir, 'src/i18n/locales/en.ts');
const amPath = path.join(clientDir, 'src/i18n/locales/am.ts');

const en = parseLocale(enPath);
const am = parseLocale(amPath);

const enKeys = new Set(getLeafKeys(en));
const amKeys = new Set(getLeafKeys(am));

let hasError = false;

// 1. Check dictionary symmetry
const missingInAm = [...enKeys].filter(k => !amKeys.has(k));
const missingInEn = [...amKeys].filter(k => !enKeys.has(k));

if (missingInAm.length > 0) {
  console.error(`❌ Missing in AM (${missingInAm.length}):`, missingInAm);
  hasError = true;
}
if (missingInEn.length > 0) {
  console.error(`❌ Missing in EN (${missingInEn.length}):`, missingInEn);
  hasError = true;
}

// 2. Scan client source code for t('...') calls
function scanFiles(dir) {
  let files = [];
  for (const item of fs.readdirSync(dir)) {
    const full = path.join(dir, item);
    const stat = fs.statSync(full);
    if (stat.isDirectory()) {
      if (!['node_modules', 'dist', 'coverage'].includes(item)) {
        files = files.concat(scanFiles(full));
      }
    } else if (full.endsWith('.vue') || full.endsWith('.ts')) {
      if (!full.includes('locales') && !full.includes('__tests__') && !full.includes('.spec.ts')) {
        files.push(full);
      }
    }
  }
  return files;
}

const sourceFiles = scanFiles(path.join(clientDir, 'src'));
const missingCodeCalls = [];

const tRegex = /\bt\(\s*['"]([a-zA-Z0-9_.-]+)['"]/g;

for (const file of sourceFiles) {
  const content = fs.readFileSync(file, 'utf8');
  let m;
  while ((m = tRegex.exec(content)) !== null) {
    const key = m[1];
    if (!enKeys.has(key)) {
      missingCodeCalls.push({
        file: path.relative(clientDir, file),
        key
      });
    }
  }
}

if (missingCodeCalls.length > 0) {
  console.error(`❌ Undefined translation keys invoked in code (${missingCodeCalls.length}):`);
  missingCodeCalls.forEach(c => console.error(`  ${c.file}: ${c.key}`));
  hasError = true;
}

if (hasError) {
  console.error('\n🚨 i18n Audit FAILED: Missing translation keys detected.');
  process.exit(1);
} else {
  console.log(`\n✅ i18n Audit PASSED:`);
  console.log(`  - Total localized keys: ${enKeys.size} (100% symmetric EN <-> AM)`);
  console.log(`  - Files verified: ${sourceFiles.length}`);
  console.log(`  - Zero raw or undefined translation keys detected.`);
  process.exit(0);
}
