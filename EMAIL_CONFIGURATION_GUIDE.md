# 📧 Email Configuration Guide

## Current Status: Emails Not Sending ❌

Your system is currently set to **'log'** mode, which means emails are not actually being sent. Follow this guide to enable real email sending.

---

## 🚀 Quick Fix (3 Steps)

### Step 1: Configure Email in .env File

Open your `.env` file and **replace** or **add** these settings:

### 🟢 **Option A: Gmail (Recommended for Testing)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Internlink System"
```

**⚠️ Gmail Setup Required:**
1. Enable **2-Factor Authentication** on your Google account
2. Go to https://myaccount.google.com/apppasswords
3. Select "Mail" and your device
4. Copy the 16-character password (remove spaces)
5. Use that in `MAIL_PASSWORD` (not your regular password!)

---

### 🔵 **Option B: Mailtrap (Best for Testing - Catches All Emails)**

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@internlink.local
MAIL_FROM_NAME="Internlink System"
```

**Setup:**
1. Sign up FREE at https://mailtrap.io
2. Go to "Email Testing" → "Inboxes" → "My Inbox"
3. Copy SMTP credentials
4. All emails will be caught (safe for testing!)

---

### 🟣 **Option C: Microsoft Outlook/Office 365**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=starttls
MAIL_FROM_ADDRESS=your-email@outlook.com
MAIL_FROM_NAME="Internlink System"
```

---

### Step 2: Clear Configuration Cache

After editing `.env`, run:

```bash
php artisan config:clear
```

---

### Step 3: Test Email Sending

Run this command to test:

```bash
php artisan tinker
```

Then in tinker, run:
```php
\Illuminate\Support\Facades\Mail::raw('Test email from Internlink', function($msg) {
    $msg->to('your-email@example.com')->subject('Test Email');
});
```

Type `exit` to quit tinker.

---

## ✅ Verification

### Check if Email Configuration is Loaded

```bash
php artisan config:show mail.mailers.smtp
```

You should see your SMTP settings.

---

## 🔄 Re-send Registration Emails

Since emails weren't sent for your previous registrations, you have two options:

### Option 1: Manual Password Reset
Users can use the "Forgot Password" link on login page.

### Option 2: Resend Notifications Manually

I can create a command to resend registration emails to users who haven't received them yet.

---

## 📋 Common Email Providers

| Provider | MAIL_HOST | MAIL_PORT | MAIL_ENCRYPTION |
|----------|-----------|-----------|-----------------|
| **Gmail** | smtp.gmail.com | 587 | tls |
| **Outlook** | smtp-mail.outlook.com | 587 | starttls |
| **Yahoo** | smtp.mail.yahoo.com | 587 | tls |
| **Mailgun** | smtp.mailgun.org | 587 | tls |
| **SendGrid** | smtp.sendgrid.net | 587 | tls |
| **Mailtrap** | sandbox.smtp.mailtrap.io | 2525 | tls |

---

## 🛠️ Troubleshooting

### "Connection could not be established"
- Check firewall settings
- Verify SMTP credentials
- Try different port (465 for SSL, 587 for TLS)
- Ensure internet connection

### Gmail "Less secure app" error
- Don't use regular password
- Must use App Password (requires 2FA)
- Enable IMAP in Gmail settings

### Emails going to spam
- Set proper `MAIL_FROM_ADDRESS`
- Use domain email (not Gmail) for production
- Configure SPF/DKIM records (production)

### Outlook/Office 365 issues
- Use `starttls` encryption
- Enable SMTP AUTH in Office 365 admin
- May need to enable "Modern Authentication"

---

## 🎯 For Your Specific Case

Your students are:
- **Muhammad Hariz** - mharizh03@gmail.com
- **Iman Nopie** - mhareezh@gmail.com

### Quick Test Plan:

1. **Configure Email** (Use Mailtrap for testing)
   - Sign up at mailtrap.io
   - Get SMTP credentials
   - Add to `.env`
   - Run `php artisan config:clear`

2. **Test Email**
   ```bash
   php artisan tinker --execute="
   \Illuminate\Support\Facades\Mail::raw('This is a test', function(\$msg) {
       \$msg->to('mharizh03@gmail.com')->subject('Test from Internlink');
   });
   echo 'Email sent!';
   "
   ```

3. **Register New Test User**
   - Add one test user via bulk registration
   - Email should be sent immediately now (queueing disabled)
   - Check Mailtrap inbox or real email

4. **For Previous Users**
   - They can use "Forgot Password" on login page
   - Or you can manually send them their passwords
   - Or I can create a resend command

---

## 📝 What Changed Today

✅ **Disabled email queueing** - Emails now send immediately
✅ **Fixed User Directory error** - Bulk registration works
✅ **CSV templates created** - Ready to use

🔧 **Still needed:** Configure SMTP in `.env` file

---

## 🎓 Production Recommendations

For production use:
1. ✅ Use domain email (e.g., noreply@internlink.edu.my)
2. ✅ Use reliable SMTP service (not Gmail)
3. ✅ Configure SPF and DKIM records
4. ✅ Enable queue for better performance
5. ✅ Monitor email delivery rates
6. ✅ Set up email bounce handling

**Recommended Services:**
- **SendGrid** (free 100 emails/day)
- **Mailgun** (free 5,000 emails/month)
- **Amazon SES** (very cheap)
- **Postmark** (reliable, paid)

---

## 🆘 Need Help?

If you're stuck:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Test email config: `php artisan config:show mail`
3. Verify .env loaded: `php artisan config:cache`
4. Test connection: `php artisan tinker` (then send test email)

---

## 📚 Example .env Configuration

```env
APP_NAME="Internlink"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Email Settings (Choose one option and uncomment)

# Gmail (requires App Password)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Google Maps (you already have this)
GOOGLE_MAPS_API_KEY=your-api-key-here
```

---

**Ready to configure?** Choose an option above and update your `.env` file!

Once configured, emails will be sent immediately when users are registered. 🚀

