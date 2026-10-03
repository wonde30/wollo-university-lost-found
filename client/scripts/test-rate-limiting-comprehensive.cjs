const axios = require('axios');

const BASE_URL = 'http://127.0.0.1:8000/api/v1';

async function probeEndpoint({ name, endpoint, method = 'post', getPayload, maxAttempts, expectedAllowed, expectedBlocked, bucketKey }) {
  console.log(`\n==================================================`);
  console.log(`PROBING: ${name} (${endpoint})`);
  console.log(`Bucket Key: ${bucketKey}`);
  console.log(`==================================================`);

  let allowed = 0;
  let blockedRequestNum = null;
  let blockedStatus = null;
  let retryAfter = null;

  for (let i = 1; i <= maxAttempts; i++) {
    const payload = getPayload(i);
    try {
      const res = await axios({
        method,
        url: `${BASE_URL}${endpoint}`,
        data: payload,
        validateStatus: () => true,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        }
      });

      if (res.status === 429) {
        if (!blockedRequestNum) {
          blockedRequestNum = i;
          blockedStatus = res.status;
          retryAfter = res.data?.retry_after || res.headers['retry-after'] || null;
        }
        console.log(`  Req #${i}: HTTP 429 BLOCKED (retry_after: ${retryAfter}s)`);
      } else {
        allowed++;
        console.log(`  Req #${i}: HTTP ${res.status} ALLOWED`);
      }
    } catch (err) {
      console.error(`  Req #${i}: ERROR ${err.message}`);
    }
  }

  console.log(`Result for ${name}:`);
  console.log(`  Allowed requests: ${allowed}`);
  console.log(`  First blocked request: #${blockedRequestNum}`);
  console.log(`  HTTP status: ${blockedStatus}`);
  console.log(`  retry_after: ${retryAfter}`);
  return { name, endpoint, allowed, blockedRequestNum, blockedStatus, retryAfter, bucketKey };
}

async function runAll() {
  console.log('Starting Rate Limiting Certification Probes...');

  // 1. FORGOT PASSWORD (3 attempts/minute per email+IP)
  const forgotRes = await probeEndpoint({
    name: 'FORGOT PASSWORD',
    endpoint: '/auth/forgot-password',
    getPayload: () => ({ email: 'rate_forgot@wu.edu.et' }),
    maxAttempts: 5,
    expectedAllowed: 3,
    expectedBlocked: 4,
    bucketKey: 'email|ip',
  });

  // Isolation check for forgot password with another email
  console.log('\nTesting FORGOT PASSWORD isolation with separate email...');
  const forgotIso = await axios.post(`${BASE_URL}/auth/forgot-password`, { email: 'another_forgot@wu.edu.et' }, { validateStatus: () => true });
  console.log(`  Another email status: HTTP ${forgotIso.status} (Isolated: ${forgotIso.status !== 429})`);

  // 2. RESEND VERIFICATION (2 requests/minute per email+IP)
  const resendRes = await probeEndpoint({
    name: 'RESEND VERIFICATION',
    endpoint: '/auth/resend-verification',
    getPayload: () => ({ email: 'rate_resend@wu.edu.et' }),
    maxAttempts: 4,
    expectedAllowed: 2,
    expectedBlocked: 3,
    bucketKey: 'email|ip',
  });

  // Isolation check for resend verification
  console.log('\nTesting RESEND VERIFICATION isolation with separate email...');
  const resendIso = await axios.post(`${BASE_URL}/auth/resend-verification`, { email: 'another_resend@wu.edu.et' }, { validateStatus: () => true });
  console.log(`  Another email status: HTTP ${resendIso.status} (Isolated: ${resendIso.status !== 429})`);

  // 3. RESET PASSWORD (3 requests/minute per email+IP via password-reset throttle)
  const resetRes = await probeEndpoint({
    name: 'RESET PASSWORD',
    endpoint: '/auth/reset-password',
    getPayload: () => ({ email: 'rate_reset@wu.edu.et', token: 'invalid', password: 'Password123!', password_confirmation: 'Password123!' }),
    maxAttempts: 5,
    expectedAllowed: 3,
    expectedBlocked: 4,
    bucketKey: 'email|ip',
  });

  // 4. VERIFY EMAIL (10 attempts/minute per email+IP via otp-verify throttle)
  const verifyEmailRes = await probeEndpoint({
    name: 'VERIFY EMAIL',
    endpoint: '/auth/verify-email',
    getPayload: () => ({ email: 'rate_verify@wu.edu.et', otp: '123456' }),
    maxAttempts: 12,
    expectedAllowed: 10,
    expectedBlocked: 11,
    bucketKey: 'email|ip',
  });

  // 5. VERIFY PASSWORD RESET (10 attempts/minute per email+IP via otp-verify throttle)
  const verifyPwRes = await probeEndpoint({
    name: 'VERIFY PASSWORD RESET',
    endpoint: '/auth/verify-password-reset',
    getPayload: () => ({ email: 'rate_verifypw@wu.edu.et', otp: '123456' }),
    maxAttempts: 12,
    expectedAllowed: 10,
    expectedBlocked: 11,
    bucketKey: 'email|ip',
  });

  // 6. LOGIN (5 attempts/minute per user+IP, and 10 attempts/minute per IP)
  const loginRes = await probeEndpoint({
    name: 'LOGIN (per user+IP)',
    endpoint: '/auth/login',
    getPayload: () => ({ email: 'rate_login_user@wu.edu.et', password: 'wrongpassword' }),
    maxAttempts: 7,
    expectedAllowed: 5,
    expectedBlocked: 6,
    bucketKey: 'email|ip + ip',
  });

  // 7. REGISTER (5 attempts/minute per IP)
  const registerRes = await probeEndpoint({
    name: 'REGISTER',
    endpoint: '/auth/register',
    getPayload: (i) => ({ full_name: `Student Rate ${i}`, email: `student_rate_${i}_${Date.now()}@wu.edu.et`, password: 'Password123!', password_confirmation: 'Password123!' }),
    maxAttempts: 7,
    expectedAllowed: 5,
    expectedBlocked: 6,
    bucketKey: 'ip',
  });

  console.log('\n==================================================');
  console.log('RATE LIMITING PROBE RUN COMPLETE');
  console.log('==================================================');
}

runAll().catch(err => console.error(err));
