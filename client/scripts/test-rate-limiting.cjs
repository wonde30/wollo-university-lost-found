const http = require('http');

function makeRequest(path, method, body, headers = {}) {
  return new Promise((resolve, reject) => {
    const postData = JSON.stringify(body || {});
    const req = http.request({
      hostname: '127.0.0.1',
      port: 8000,
      path: path,
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Content-Length': Buffer.byteLength(postData),
        ...headers,
      },
    }, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(data);
        } catch {}
        resolve({
          status: res.statusCode,
          headers: res.headers,
          data: json || data,
        });
      });
    });

    req.on('error', reject);
    req.write(postData);
    req.end();
  });
}

async function run() {
  console.log('=== REAL RATE-LIMITING VERIFICATION ===\n');

  // 1. LOGIN RATE LIMIT (Limit: 5 per minute per email|ip)
  console.log('Testing LOGIN rate limit (/api/v1/auth/login)...');
  const targetEmail = 'ratelimit.user@wu.edu.et';
  let hit429 = false;
  let retryAfter = null;

  for (let i = 1; i <= 7; i++) {
    const res = await makeRequest('/api/v1/auth/login', 'POST', {
      email: targetEmail,
      password: 'wrong-password-test',
    });
    console.log(`  Attempt ${i}: Status = ${res.status}`);
    if (res.status === 429) {
      hit429 = true;
      retryAfter = res.data?.retry_after || res.headers['retry-after'];
      console.log(`  -> 429 Triggered successfully on attempt ${i}! retry_after: ${retryAfter}s`);
      break;
    }
  }

  if (!hit429) {
    throw new Error('FAILED: Login rate limit 429 was not triggered within 7 attempts');
  }

  // Verify unrelated user is NOT blocked
  console.log('\nVerifying login isolation (unrelated user)...');
  const unrelatedRes = await makeRequest('/api/v1/auth/login', 'POST', {
    email: 'unrelated.student@wu.edu.et',
    password: 'wrong-password-test',
  });
  console.log(`  Unrelated user login attempt: Status = ${unrelatedRes.status}`);
  if (unrelatedRes.status === 429) {
    throw new Error('FAILED: Login rate limit bucket incorrectly blocked an unrelated user!');
  }
  console.log('  -> Unrelated user is NOT blocked (Isolation verified)!');

  // 2. FORGOT PASSWORD (Limit: 3 per minute)
  console.log('\nTesting FORGOT PASSWORD rate limit (/api/v1/auth/forgot-password)...');
  const forgotEmail = 'forgot.test@wu.edu.et';
  let forgot429 = false;

  for (let i = 1; i <= 5; i++) {
    const res = await makeRequest('/api/v1/auth/forgot-password', 'POST', {
      email: forgotEmail,
    });
    console.log(`  Attempt ${i}: Status = ${res.status}`);
    if (res.status === 429) {
      forgot429 = true;
      console.log(`  -> 429 Triggered successfully on attempt ${i}!`);
      break;
    }
  }
  if (!forgot429) {
    throw new Error('FAILED: Forgot password rate limit 429 was not triggered');
  }

  // 3. OTP RESEND RATE LIMIT (Limit: 2 per minute)
  console.log('\nTesting OTP RESEND rate limit (/api/v1/auth/resend-verification)...');
  let otp429 = false;
  for (let i = 1; i <= 4; i++) {
    const res = await makeRequest('/api/v1/auth/resend-verification', 'POST', {
      email: 'otp.test@wu.edu.et',
    });
    console.log(`  Attempt ${i}: Status = ${res.status}`);
    if (res.status === 429) {
      otp429 = true;
      console.log(`  -> 429 Triggered successfully on attempt ${i}!`);
      break;
    }
  }
  if (!otp429) {
    throw new Error('FAILED: OTP resend rate limit 429 was not triggered');
  }

  // 4. REGISTER RATE LIMIT (Limit: 5 per minute by IP)
  console.log('\nTesting REGISTER rate limit (/api/v1/auth/register)...');
  let register429 = false;
  for (let i = 1; i <= 7; i++) {
    const res = await makeRequest('/api/v1/auth/register', 'POST', {
      name: 'Test Reg',
      email: `reg${i}@wu.edu.et`,
      password: 'Password123!',
      password_confirmation: 'Password123!',
    });
    console.log(`  Attempt ${i}: Status = ${res.status}`);
    if (res.status === 429) {
      register429 = true;
      console.log(`  -> 429 Triggered successfully on attempt ${i}!`);
      break;
    }
  }
  if (!register429) {
    throw new Error('FAILED: Register rate limit 429 was not triggered');
  }

  console.log('\n=== ALL RATE LIMIT TESTS COMPLETED AND VERIFIED ===');
}

run().catch((err) => {
  console.error(err);
  process.exit(1);
});
