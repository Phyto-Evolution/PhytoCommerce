'use strict';

const crypto = require('crypto');

const PAYU_KEY  = process.env.PAYU_KEY;
const PAYU_SALT = process.env.PAYU_SALT;
const PAYU_ENV  = process.env.PAYU_ENV || 'test';

const PAYU_URLS = {
  test: 'https://test.payu.in/_payment',
  prod: 'https://secure.payu.in/_payment',
};

/**
 * Verify PayU response hash.
 *
 * PayU sends a hash in the response after payment.
 * Verification formula (response):
 *   sha512(SALT|status|||||||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|KEY)
 *
 * @param {object} payload - Full PayU POST body
 * @returns {boolean}
 */
function verifyWebhookHash(payload) {
  if (!PAYU_KEY || !PAYU_SALT) {
    throw new Error('PAYU_KEY and PAYU_SALT must be set in environment');
  }

  const {
    txnid, amount, productinfo, firstname, email,
    udf1 = '', udf2 = '', udf3 = '', udf4 = '', udf5 = '',
    status, hash,
  } = payload;

  // Reverse hash: SALT|status|||||||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|KEY
  const reverseHashString = [
    PAYU_SALT,
    status,
    '', '', '', '', '', '', '', '', '', '',  // 10 empty pipes for additional fields
    udf5, udf4, udf3, udf2, udf1,
    email,
    firstname,
    productinfo,
    amount,
    txnid,
    PAYU_KEY,
  ].join('|');

  const computed = crypto
    .createHash('sha512')
    .update(reverseHashString)
    .digest('hex');

  return computed === hash;
}

/**
 * Build the hash string for initiating a PayU payment.
 * Formula: sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
 */
function buildPaymentHash({ txnid, amount, productinfo, firstname, email,
                            udf1 = '', udf2 = '', udf3 = '', udf4 = '', udf5 = '' }) {
  if (!PAYU_KEY || !PAYU_SALT) {
    throw new Error('PAYU_KEY and PAYU_SALT must be set in environment');
  }

  const hashString = [
    PAYU_KEY, txnid, amount, productinfo, firstname, email,
    udf1, udf2, udf3, udf4, udf5,
    '', '', '', '', '',
    PAYU_SALT,
  ].join('|');

  return crypto.createHash('sha512').update(hashString).digest('hex');
}

/**
 * Determine plan details from PayU productinfo field.
 * productinfo is set by us when creating the payment: "subdomain|{slug}" or "custom|{slug}"
 */
function parsePlanFromProductInfo(productinfo) {
  if (!productinfo) return null;
  const [plan, slug] = productinfo.split('|');
  if (!['subdomain', 'custom'].includes(plan) || !slug) return null;
  return { plan, slug };
}

/**
 * Get plan pricing.
 */
function getPlanPricing(plan) {
  if (plan === 'subdomain') {
    return { monthly_amount: 349, txn_fee_pct: 1.0 };
  }
  if (plan === 'custom') {
    return { monthly_amount: 499, txn_fee_pct: 2.0 };
  }
  throw new Error(`Unknown plan: ${plan}`);
}

function getPaymentUrl() {
  return PAYU_URLS[PAYU_ENV] || PAYU_URLS.test;
}

module.exports = {
  verifyWebhookHash,
  buildPaymentHash,
  parsePlanFromProductInfo,
  getPlanPricing,
  getPaymentUrl,
  PAYU_KEY,
};
