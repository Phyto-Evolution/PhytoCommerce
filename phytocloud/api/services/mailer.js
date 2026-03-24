'use strict';

const Mailgun = require('mailgun.js');
const FormData = require('form-data');

const MAILGUN_API_KEY = process.env.MAILGUN_API_KEY;
const MAILGUN_DOMAIN  = process.env.MAILGUN_DOMAIN  || 'mg.carnivorousplants.in';
const FROM_EMAIL      = process.env.FROM_EMAIL      || 'noreply@carnivorousplants.in';
const FROM_NAME       = process.env.FROM_NAME       || 'Phyto E-Commerce';

let mg = null;

function getClient() {
  if (!mg) {
    if (!MAILGUN_API_KEY) {
      console.warn('[mailer] MAILGUN_API_KEY not set — emails will not be sent');
      return null;
    }
    const mailgun = new Mailgun(FormData);
    mg = mailgun.client({ username: 'api', key: MAILGUN_API_KEY });
  }
  return mg;
}

async function sendMail({ to, subject, html, text }) {
  const client = getClient();
  if (!client) return;

  try {
    await client.messages.create(MAILGUN_DOMAIN, {
      from: `${FROM_NAME} <${FROM_EMAIL}>`,
      to,
      subject,
      html,
      text,
    });
    console.log(`[mailer] Sent "${subject}" to ${to}`);
  } catch (err) {
    console.error(`[mailer] Failed to send to ${to}:`, err.message);
    // Don't throw — email failure should not break provisioning
  }
}

// ── Email templates ───────────────────────────────────────────────────────────

async function sendWelcomeEmail(tenant) {
  const storeUrl = tenant.plan === 'subdomain'
    ? `https://${tenant.subdomain}`
    : `https://${tenant.domain}`;

  const adminUrl = `${storeUrl}/${tenant.ps_admin_path}`;

  const html = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1a1a1a; max-width: 600px; margin: 0 auto; padding: 20px; }
  h1 { color: #2d5a27; border-bottom: 2px solid #2d5a27; padding-bottom: 10px; }
  .card { background: #f5f9f4; border-left: 4px solid #2d5a27; padding: 16px; margin: 20px 0; border-radius: 4px; }
  .cred { font-family: monospace; background: #fff; padding: 4px 8px; border-radius: 3px; border: 1px solid #ddd; }
  a.btn { display: inline-block; background: #2d5a27; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; margin-top: 16px; }
  .footer { font-size: 12px; color: #666; margin-top: 40px; border-top: 1px solid #eee; padding-top: 16px; }
</style></head>
<body>
  <h1>🌿 Your store is live!</h1>
  <p>Your Phyto E-Commerce store is up and running. Here are your details:</p>

  <div class="card">
    <strong>Store URL:</strong><br>
    <a href="${storeUrl}">${storeUrl}</a>
  </div>

  <div class="card">
    <strong>Admin Panel:</strong><br>
    <a href="${adminUrl}">${adminUrl}</a><br><br>
    <strong>Email:</strong> <span class="cred">${tenant.ps_admin_email}</span><br>
    <strong>Password:</strong> <span class="cred">${tenant.ps_admin_password}</span>
  </div>

  <p><strong>Save these credentials</strong> — change your admin password after first login.</p>

  <a class="btn" href="${adminUrl}">Go to your admin panel →</a>

  <div class="card" style="margin-top: 32px;">
    <strong>Your plan:</strong>
    ${tenant.plan === 'subdomain'
      ? `Rs 349/month + 1% transaction fee<br>Subdomain: ${tenant.subdomain}`
      : `Rs 499/month + 2% transaction fee<br>Connect your domain: ${tenant.domain}`
    }
  </div>

  <div class="footer">
    Questions? Reply to this email or visit <a href="https://phytolabs.in">phytolabs.in</a>.<br>
    Phyto E-Commerce · yourshop.phytolabs.in
  </div>
</body>
</html>
  `;

  await sendMail({
    to: tenant.email,
    subject: `Your Phyto store is live — ${storeUrl}`,
    html,
    text: `Your store is live at ${storeUrl}. Admin panel: ${adminUrl} | Email: ${tenant.ps_admin_email} | Password: ${tenant.ps_admin_password}`,
  });
}

async function sendSuspensionEmail(tenant) {
  const html = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1a1a1a; max-width: 600px; margin: 0 auto; padding: 20px; }
  h1 { color: #b84c00; }
  .card { background: #fff8f5; border-left: 4px solid #b84c00; padding: 16px; margin: 20px 0; border-radius: 4px; }
  a.btn { display: inline-block; background: #b84c00; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; margin-top: 16px; }
</style></head>
<body>
  <h1>⚠️ Your store has been suspended</h1>
  <p>We were unable to process your monthly payment of Rs ${tenant.monthly_amount}.</p>

  <div class="card">
    Your store <strong>${tenant.subdomain || tenant.domain}</strong> is currently suspended.<br><br>
    You have <strong>7 days</strong> to update your payment details before your store is permanently removed.
  </div>

  <a class="btn" href="https://yourshop.phytolabs.in/billing">Update payment details →</a>

  <p style="margin-top: 24px; font-size: 14px; color: #666;">
    If you believe this is an error, reply to this email and we'll sort it out.
  </p>
</body>
</html>
  `;

  await sendMail({
    to: tenant.email,
    subject: 'Payment failed — your Phyto store is suspended',
    html,
    text: `Your store at ${tenant.subdomain || tenant.domain} has been suspended due to a failed payment. You have 7 days to update your payment details.`,
  });
}

async function sendCancellationEmail(tenant) {
  const html = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #1a1a1a; max-width: 600px; margin: 0 auto; padding: 20px; }
  h1 { color: #555; }
  .card { background: #f5f5f5; border-left: 4px solid #999; padding: 16px; margin: 20px 0; border-radius: 4px; }
</style></head>
<body>
  <h1>Your store has been cancelled</h1>
  <p>Your Phyto E-Commerce subscription has been cancelled.</p>

  <div class="card">
    Your store data will be <strong>permanently deleted in 30 days</strong>.<br><br>
    To export your data before deletion, contact us at
    <a href="mailto:support@phytolabs.in">support@phytolabs.in</a>
  </div>

  <p>Thank you for being a Phyto E-Commerce customer. We hope to see you again.</p>
</body>
</html>
  `;

  await sendMail({
    to: tenant.email,
    subject: 'Your Phyto store has been cancelled',
    html,
    text: `Your store subscription has been cancelled. Your data will be deleted in 30 days. Contact support@phytolabs.in to export your data.`,
  });
}

async function sendProvisioningErrorEmail(tenant, errorMsg) {
  await sendMail({
    to: tenant.email,
    subject: 'Issue setting up your Phyto store — we\'re on it',
    html: `<p>There was an issue provisioning your store. Our team has been alerted and will resolve this within 24 hours. Error: ${errorMsg}</p>`,
    text: `There was an issue provisioning your store. Our team has been alerted. Error: ${errorMsg}`,
  });
}

module.exports = {
  sendWelcomeEmail,
  sendSuspensionEmail,
  sendCancellationEmail,
  sendProvisioningErrorEmail,
};
