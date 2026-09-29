# GitHub Actions Deployment Setup

This theme uses GitHub Actions for manual deployment to your WordPress server.

## Required GitHub Secrets

Go to your repository settings → Secrets and variables → Actions, then add these secrets:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `SSH_PRIVATE_KEY` | Your SSH private key for server access | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `SFTP_HOST` | Your server hostname or IP | `aws-ec2-m-wp1.anasriaz.com` |
| `SFTP_USERNAME` | SSH/SFTP username | `awsairuniversity-ssh` |
| `SFTP_PORT` | SSH port (optional, defaults to 22) | `22` |
| `REMOTE_PATH` | Absolute path to theme directory on server | `/home/awsairuniversity/htdocs/awsairuniversity.com/wp-content/themes/aws-sbg-theme` |

## How to Set Up SSH Key

1. **Generate a new SSH key pair** (if you don't have one):
   ```bash
   ssh-keygen -t ed25519 -C "github-actions@aws-sbg-theme"
   ```

2. **Add the public key to your server**:
   ```bash
   ssh-copy-id -i ~/.ssh/id_ed25519.pub awsairuniversity-ssh@aws-ec2-m-wp1.anasriaz.com
   ```

3. **Add the private key to GitHub Secrets**:
   - Copy the contents of `~/.ssh/id_ed25519`
   - Paste it as the `SSH_PRIVATE_KEY` secret in GitHub

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
