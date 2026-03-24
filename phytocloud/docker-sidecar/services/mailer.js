'use strict';

const Mailgun  = require('mailgun.js');
const FormData = require('form-data');

const MAILGUN_API_KEY = process.env.MAILGUN_API_KEY;
const MAILGUN_DOMAIN  = process.env.MAILGUN_DOMAIN  || 'mg.carnivorousplants.in';
const FROM_EMAIL      = process.env.FROM_EMAIL      || 'noreply@carnivorousplants.in';
const FROM_NAME       = process.env.FROM_NAME       || 'Phyto E-Commerce';

let mg = null;
function client() {
  if (!mg && MAILGUN_API_KEY) {
    const mailgun = new Mailgun(FormData);
    mg = mailgun.client({ username: 'api', key: MAILGUN_API_KEY });
  }
  return mg;
}

async function send({ to, subject, html, text }) {
  const c = client();
  if (!c) { console.warn('[mailer] No Mailgun key — skipping:', subject); return; }
  try {
    await c.messages.create(MAILGUN_DOMAIN, {
      from: `${FROM_NAME} <${FROM_EMAIL}>`, to, subject, html, text,
    });
    console.log(`[mailer] Sent to ${to}: ${subject}`);
  } catch (err) {
    console.error(`[mailer] Failed to ${to}:`, err.message);
  }
}

async function sendWelcomeEmail(t) {
  const storeUrl = t.plan === 'subdomain'
    ? `https://${t.subdomain}` : `https://${t.domain}`;
  const adminUrl = `${storeUrl}/${t.ps_admin_path}`;

  await send({
    to: t.email,
    subject: `Your Phyto store is live — ${storeUrl}`,
    html: `
<body style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:20px">
<h2 style="color:#2d5a27">🌿 Your store is live!</h2>
<p><strong>Store:</strong> <a href="${storeUrl}">${storeUrl}</a></p>
<p><strong>Admin:</strong> <a href="${adminUrl}">${adminUrl}</a></p>
<p><strong>Login:</strong> ${t.ps_admin_email} / <code>${t.ps_admin_password}</code></p>
<p style="color:#888;font-size:12px">Change your password after first login. Questions? support@phytolabs.in</p>
</body>`,
    text: `Store: ${storeUrl}\nAdmin: ${adminUrl}\nEmail: ${t.ps_admin_email}\nPassword: ${t.ps_admin_password}`,
  });
}

async function sendSuspensionEmail(t) {
  await send({
    to: t.email,
    subject: 'Payment failed — your Phyto store is suspended',
    html: `<body style="font-family:sans-serif;padding:20px"><h2 style="color:#b84c00">⚠️ Store suspended</h2><p>Payment of Rs ${t.monthly_amount} failed. You have <strong>7 days</strong> to update your details before your store is removed.</p><p><a href="https://yourshop.phytolabs.in/billing">Update payment →</a></p></body>`,
    text: `Your store at ${t.subdomain || t.domain} is suspended. Update payment at yourshop.phytolabs.in/billing`,
  });
}

async function sendCancellationEmail(t) {
  await send({
    to: t.email,
    subject: 'Your Phyto store has been cancelled',
    html: `<body style="font-family:sans-serif;padding:20px"><h2>Subscription cancelled</h2><p>Your store data will be deleted in <strong>30 days</strong>. Email support@phytolabs.in to export your data.</p></body>`,
    text: `Subscription cancelled. Data deleted in 30 days. Contact support@phytolabs.in to export.`,
  });
}

async function sendProvisioningErrorEmail(t, err) {
  await send({
    to: t.email,
    subject: "Issue setting up your Phyto store — we're on it",
    html: `<p>There was an issue provisioning your store. Our team has been alerted and will resolve this within 24 hours.</p>`,
    text: `Provisioning error for ${t.slug}: ${err}. Our team has been alerted.`,
  });
}

module.exports = { sendWelcomeEmail, sendSuspensionEmail, sendCancellationEmail, sendProvisioningErrorEmail };
