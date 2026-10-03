import fs from 'fs';
import path from 'path';

const enPath = path.resolve('c:/Users/W/Desktop/wollo-lost-found/client/src/i18n/locales/en.ts');
const amPath = path.resolve('c:/Users/W/Desktop/wollo-lost-found/client/src/i18n/locales/am.ts');

function parseLocale(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  content = content.replace(/export\s+const\s+\w+\s*=\s*/, '').replace(/;\s*$/, '');
  return JSON.parse(content);
}

function saveLocale(filePath, varName, obj) {
  const content = `export const ${varName} = ${JSON.stringify(obj, null, 2)};\n`;
  fs.writeFileSync(filePath, content, 'utf8');
}

const en = parseLocale(enPath);
const am = parseLocale(amPath);

function setDeep(obj, keyPath, val) {
  const parts = keyPath.split('.');
  let curr = obj;
  for (let i = 0; i < parts.length - 1; i++) {
    if (!curr[parts[i]]) curr[parts[i]] = {};
    curr = curr[parts[i]];
  }
  curr[parts[parts.length - 1]] = val;
}

const additions = [
  {
    key: 'nav.analytics',
    en: 'Analytics',
    am: 'ትንታኔዎች'
  },
  {
    key: 'nav.staffOperations',
    en: 'Staff Operations',
    am: 'የሰራተኞች ተግባራት'
  },
  {
    key: 'common.selected',
    en: 'selected',
    am: 'ተመርጧል'
  },
  {
    key: 'common.refreshed',
    en: 'Refreshed successfully',
    am: 'በተሳካ ሁኔታ ታድሷል'
  },
  {
    key: 'common.chartError',
    en: 'Failed to load chart metrics',
    am: 'የቻርት መረጃዎችን መጫን አልተቻለም'
  },
  {
    key: 'common.trendError',
    en: 'Failed to load trend data',
    am: 'የአዝማሚያ መረጃዎችን መጫን አልተቻለም'
  },
  {
    key: 'admin.reports.totalReports',
    en: 'Total Reports',
    am: 'አጠቃላይ ሪፖርቶች'
  },
  {
    key: 'admin.reports.readyToDownload',
    en: 'Ready to Download',
    am: 'ለማውረድ ዝግጁ'
  },
  {
    key: 'admin.reports.readyDescription',
    en: 'Processed & ready for export',
    am: 'ተዘጋጅቶ ለኤክስፖርት ዝግጁ የሆነ'
  },
  {
    key: 'admin.reports.processingQueue',
    en: 'Processing Queue',
    am: 'በሂደት ላይ ያለ ወረፋ'
  },
  {
    key: 'admin.reports.recoveryRate',
    en: 'Recovery Rate',
    am: 'የመልሶ ማግኛ ምጣኔ'
  },
  {
    key: 'claims.proofAndExplanation',
    en: 'Proof & Explanation:',
    am: 'ማስረጃ እና ማብራሪያ፦'
  },
  {
    key: 'claims.evidenceAttached',
    en: '{count} evidence file(s) attached',
    am: '{count} የማስረጃ ፋይል(ሎች) ተያይዟል'
  },
  {
    key: 'claims.noEvidenceAttached',
    en: 'No evidence attachments',
    am: 'ምንም የተያያዘ ማስረጃ የለም'
  },
  {
    key: 'common.timePeriodSelector',
    en: 'Time period selector',
    am: 'የጊዜ ገደብ መራጭ'
  },
  {
    key: 'common.exitFullscreen',
    en: 'Exit Fullscreen',
    am: 'ሙሉ ማያ ገጽን ውጣ'
  },
  {
    key: 'common.fullscreen',
    en: 'Fullscreen',
    am: 'ሙሉ ማያ ገጽ'
  },
  {
    key: 'home.noRecentItems',
    en: 'No recent items found',
    am: 'ምንም የቅርብ ጊዜ እቃዎች አልተገኙም'
  },
  {
    key: 'home.stats.safeHandover',
    en: 'Safe Handover Protocol',
    am: 'ደህንነቱ የተጠበቀ የርክክብ ሥርዓት'
  },
  {
    key: 'home.trust.tag',
    en: 'INSTITUTIONAL INTEGRITY',
    am: 'የተቋማዊ ታማኝነት ዋስትና'
  },
  {
    key: 'home.trust.title',
    en: 'Campus Property Integrity & Recovery Standards',
    am: 'የካምፓስ ንብረት ደህንነት እና መልሶ ማግኛ ደረጃዎች'
  },
  {
    key: 'home.trust.subtitle',
    en: 'Official university standards governing verified custody, fair claims, and secure return operations across all Wollo campuses.',
    am: 'በሁሉም የወሎ ዩኒቨርሲቲ ግቢዎች የተረጋገጠ የጥበቃ፣ ፍትሃዊ ይገባኛል እና ደህንነቱ የተጠበቀ የርክክብ አሰራር የሚመራባቸው ይፋዊ መርሆዎች።'
  },
  {
    key: 'home.trust.p1.title',
    en: 'Verified Chain of Custody',
    am: 'የተረጋገጠ የጥበቃ ሰንሰለት'
  },
  {
    key: 'home.trust.p1.desc',
    en: 'Every item turned in is logged with campus vault locations, officer timestamps, and immutable custody events.',
    am: 'እያንዳንዱ የተገኘ እቃ በግቢው የደህንነት ግምጃ ቤት፣ በኃላፊዎች ፊርማ እና በማይለወጥ የክስተት መዝገብ ይመዘገባል።'
  },
  {
    key: 'home.trust.p2.title',
    en: 'Cryptographic Dual Confirmation',
    am: 'ዲጂታል ባለሁለት-ደረጃ ማረጋገጫ'
  },
  {
    key: 'home.trust.p2.desc',
    en: 'Physical returns require single-use security tokens or verified officer PIN confirmation with digital PDF handover receipts.',
    am: 'እቃዎችን መረከብ ነጠላ-ጥቅም ምስጢራዊ ኮዶችን ወይም የተረጋገጠ የኃላፊ ፒን እና ዲጂታል ደረሰኝ ይጠይቃል።'
  },
  {
    key: 'home.trust.p3.title',
    en: 'Identity & Data Privacy',
    am: 'የማንነት እና የመረጃ ሚስጥራዊነት'
  },
  {
    key: 'home.trust.p3.desc',
    en: 'Sensitive serial numbers, finder contact details, and proof documents remain securely masked from public catalogs.',
    am: 'ሚስጥራዊ የመለያ ቁጥሮች፣ የአግኚው አድራሻ እና የማስረጃ ሰነዶች ከህዝባዊ ካታሎግ ተደብቀው በደህንነት ይጠበቃሉ።'
  },
  {
    key: 'admin.campuses.all',
    en: 'All Campuses',
    am: 'ሁሉም ግቢዎች'
  },
  {
    key: 'admin.reports.searchPlaceholder',
    en: 'Search reports by ID, type, or user...',
    am: 'ሪፖርቶችን በመለያ ቁጥር፣ ዓይነት ወይም ተጠቃሚ ይፈልጉ...'
  },
  {
    key: 'admin.reports.allDocuments',
    en: 'All generated export documents',
    am: 'ሁሉም የተዘጋጁ የኤክስፖርት ሰነዶች'
  },
  {
    key: 'admin.reports.queuedExports',
    en: 'Queued background exports',
    am: 'በሂደት ላይ ያሉ የኤክስፖርት ስራዎች'
  },
  {
    key: 'admin.reports.verifiedRatio',
    en: 'Verified return ratio',
    am: 'የተረጋገጠ የርክክብ ምጣኔ'
  },
  {
    key: 'admin.dashboard.charts.reportedLost',
    en: 'Reported Lost',
    am: 'የጠፋ ሪፖርት የተደረገ'
  },
  {
    key: 'admin.dashboard.charts.reportedFound',
    en: 'Reported Found',
    am: 'የተገኘ ሪፖርት የተደረገ'
  },
  {
    key: 'admin.dashboard.charts.returnedToOwner',
    en: 'Returned to Owner',
    am: 'ለባለቤቱ የተመለሰ'
  },
  {
    key: 'admin.dashboard.charts.lostActive',
    en: 'Lost (Active)',
    am: 'የጠፋ (ክፍት)'
  },
  {
    key: 'admin.dashboard.charts.foundUnclaimed',
    en: 'Found (Unclaimed)',
    am: 'የተገኘ (ያልተጠየቀ)'
  },
  {
    key: 'admin.dashboard.charts.claimedVerifying',
    en: 'Claimed & Verifying',
    am: 'ተጠይቆ በማረጋገጥ ላይ'
  },
  {
    key: 'admin.dashboard.charts.returnedOwner',
    en: 'Returned to Owner',
    am: 'ለባለቤቱ የተመለሰ'
  },
  {
    key: 'admin.dashboard.charts.refreshStatistics',
    en: 'Refresh Statistics',
    am: 'ስታቲስቲክስ አድስ'
  },
  {
    key: 'validation.requiredFields',
    en: 'Please fill in all required fields',
    am: 'እባክዎ ሁሉንም አስፈላጊ መስኮች ይሙሉ'
  },
  {
    key: 'claims.myClaims.subtitle',
    en: 'Track the status and verification details of your submitted ownership claims.',
    am: 'ያቀረቧቸውን የባለቤትነት ይገባኛል ጥያቄዎች ሁኔታ እና የማረጋገጫ ዝርዝሮች ይከታተሉ።'
  },
  {
    key: 'claims.reviewNoteLabel',
    en: 'Staff Review Note:',
    am: 'የሰራተኛ ግምገማ ማስታወሻ፦'
  },
  {
    key: 'claims.searchPlaceholder',
    en: 'Search by claim ID, item name, or reference...',
    am: 'በይገባኛል መለያ፣ የዕቃ ስም ወይም መለያ ቁጥር ይፈልጉ...'
  },
  {
    key: 'items.form.locationDetail',
    en: 'Specific Location Detail',
    am: 'የቦታው ዝርዝር መግለጫ'
  },
  {
    key: 'items.form.titlePlaceholderFound',
    en: 'e.g. Scientific Calculator Casio fx-991EX',
    am: 'ለምሳሌ ሳይንሳዊ ካልኩሌተር Casio fx-991EX'
  },
  {
    key: 'items.searchPlaceholder',
    en: 'Search my reported items by title, category, campus...',
    am: 'ሪፖርት ያደረጓቸውን እቃዎች በርዕስ፣ ምድብ፣ ግቢ ይፈልጉ...'
  },
  {
    key: 'custody.searchPlaceholder',
    en: 'Search by item title, vault, officer, or note...',
    am: 'በዕቃ ርዕስ፣ ግምጃ ቤት፣ ኃላፊ ወይም ማስታወሻ ይፈልጉ...'
  },
  {
    key: 'custody.selectLocationPlaceholder',
    en: 'Select storage location',
    am: 'የማስቀመጫ ቦታ ይምረጡ'
  },
  {
    key: 'custody.loadingLocations',
    en: 'Loading locations...',
    am: 'ቦታዎችን በመጫን ላይ...'
  }
];

additions.forEach(item => {
  setDeep(en, item.key, item.en);
  setDeep(am, item.key, item.am);
});

saveLocale(enPath, 'en', en);
saveLocale(amPath, 'am', am);

console.log(`Successfully updated en.ts and am.ts with ${additions.length} keys!`);
