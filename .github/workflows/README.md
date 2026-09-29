# GitHub Actions Deployment Setup

This theme uses GitHub Actions for manual deployment to your WordPress server.

## Required GitHub Secrets (CloudPanel SFTP)

Go to your repository settings → Secrets and variables → Actions, then add these secrets:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SFTP_HOST` | Your server IP or domain | `203.0.113.10` or `aws-ec2-m-wp1.anasriaz.com` |
| `SFTP_USERNAME` | CloudPanel SSH user | `awsairuniversity-ssh` |
| `SFTP_PASSWORD` | CloudPanel SSH user password | `your-password` |
| `SFTP_PORT` | SFTP port (optional, default 22) | `22` |
| `REMOTE_PATH` | Path to theme on server | `/home/awsairuniversity/htdocs/awsairuniversity.com/wp-content/themes/aws-sbg-theme` |

**Note**: This workflow uses password-based SFTP authentication (compatible with CloudPanel).

## How to Set Up (CloudPanel SFTP)

Since you're using CloudPanel with password-based SFTP:

1. **Get your CloudPanel SFTP credentials**:
   - Log into CloudPanel
   - Go to **SSH/FTP** → **SSH Users**
   - Note down:
     - **Host**: Your server IP or domain
     - **Username**: Your SSH user
     - **Password**: The password you set
     - **Port**: Usually `22`

2. **Add secrets to GitHub**:
   - Go to your repo → **Settings** → **Secrets and variables** → **Actions**
   - Click **New repository secret** for each:

| Secret | Value | Example |
|--------|-------|---------|
| `SFTP_HOST` | Your server IP/domain | `203.0.113.10` or `aws-ec2-m-wp1.anasriaz.com` |
| `SFTP_USERNAME` | CloudPanel SSH user | `awsairuniversity-ssh` |
| `SFTP_PASSWORD` | The password you set | `your-secure-password` |
| `SFTP_PORT` | SFTP port | `22` |
| `REMOTE_PATH` | Path to theme folder | `/home/awsairuniversity/htdocs/awsairuniversity.com/wp-content/themes/aws-sbg-theme` |

## How to Deploy

1. Go to the **Actions** tab in your GitHub repository
2. Select **"Deploy to WordPress"** workflow
3. Click **"Run workflow"**
4. Select:
   - **Environment**: `production` or `staging`
   - **Branch**: The branch you want to deploy (usually `main`)
5. Click **"Run workflow"**

## What Gets Deployed

Only **required theme files** are deployed:

| Category | Files | Status |
|----------|-------|--------|
| **Theme Core** | `style.css`, `functions.php`, `header.php`, `footer.php` | ✅ Deployed |
| **Assets** | `assets/images/`, `assets/css/`, `assets/js/`, `assets/fonts/` | ✅ Deployed |
| **PHP Code** | `includes/`, `templates/` | ✅ Deployed |
| **Translations** | `languages/` | ✅ Deployed |
| **Config** | `config/config-theme.php` | ✅ Deployed |

**Excluded** (repo-only):
- `.git/`, `.github/` - Repository files
- `docs/` - Documentation
- `AGENTS.md`, `README.md` - Project docs
- `config/seed-*.json` - Seed data
- `assets/mockups/` - Design mockups
- `.copilot-instructions.md` - Agent instructions

## Troubleshooting

### Permission Denied
- Ensure the SSH key has been added to the server
- Check that the username and host are correct

### Path Not Found
- Verify `REMOTE_PATH` is the absolute path on the server
- Ensure the directory exists and is writable

### Connection Timeout
- Check if the server's firewall allows SSH connections
- Verify the SFTP port is correct (default is 22)
