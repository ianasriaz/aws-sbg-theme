# AI Agent Collaboration Log

> **Vibe Coding Approach**: This file serves as a living document where AI agents automatically report their changes. Each agent appends their entry to maintain a complete history of modifications.

## How This Works

1. **Before starting work**: Read this file to understand the project context
2. **After completing work**: Append your entry with details of what you changed
3. **Format**: Follow the template below exactly

## Agent Entry Template

```markdown
### [YYYY-MM-DD HH:MM] - [Agent Name/Model]

**Task**: [Brief description of what was requested]

**Files Changed**:
- `path/to/file1.php` - [What changed]
- `path/to/file2.css` - [What changed]

**Summary**: [2-3 sentence summary of changes]

**Key Decisions**:
- [Any important decisions made and why]

**Notes for Future Agents**:
- [Any context that would help future agents]

---
```

---

## Change Log (Newest First)

### 2024-09-29 12:30 - GitHub Copilot (Kimi K2.5)

**Task**: Reorganize theme folder structure and setup GitHub Actions deployment

**Files Changed**:
- `functions.php` - Updated all `require_once` paths to new structure
- `templates/pages/*.php` - Moved all page templates, updated image paths
- `templates/partials/*.php` - Created header/footer partials
- `includes/features/*.php` - Moved feature modules
- `includes/helpers/*.php` - Moved helper files
- `assets/images/*` - Moved from `media/`
- `docs/*` - Moved all documentation
- `config/*` - Moved config files
- `.github/workflows/deploy.yml` - Created deployment workflow
- `header.php` & `footer.php` - Created wrappers for WordPress compatibility

**Summary**: Complete reorganization of the WordPress theme into a clean, maintainable structure with proper separation of concerns. Added GitHub Actions workflow for manual deployment and removed SFTP auto-upload.

**Key Decisions**:
- Kept `header.php` and `footer.php` in root as wrappers calling partials, so WordPress `get_header()` works
- Used `assets/images/` instead of `media/` for better WordPress convention
- Set workflow to `workflow_dispatch` for manual-only deployment

**Notes for Future Agents**:
- All image paths now use `assets/images/` instead of `media/`
- Feature files are in `includes/features/` - load via `functions.php`
- Templates are in `templates/pages/` and `templates/partials/`
- GitHub Actions secrets must be configured before deployment works

---

### 2024-09-29 12:00 - GitHub Copilot (Kimi K2.5)

**Task**: Consolidate main and master branches

**Files Changed**:
- Merged `master` → `main`
- Deleted `master` branch locally and remotely

**Summary**: Merged 13 commits from master into main and deleted the master branch to eliminate confusion. Main is now the single source of truth.

**Key Decisions**:
- Chose to keep `main` as the default branch (modern GitHub standard)
- Preserved all commit history during merge

**Notes for Future Agents**:
- Only `main` branch exists now
- `origin/HEAD` points to `origin/main`

---

## Project Structure Reference

```
aws-sbg-theme/
├── assets/              # Static assets
│   ├── css/
│   ├── fonts/
│   ├── images/         # Was: media/
│   ├── js/
│   └── mockups/
├── config/             # Configuration files
├── docs/               # Documentation
├── includes/
│   ├── admin/          # Admin functionality
│   ├── features/       # Feature modules
│   ├── helpers/        # Helper functions
│   └── post-types/     # Custom post types
├── languages/          # Translations
├── templates/
│   ├── pages/          # Page templates
│   └── partials/       # Template parts
├── .github/workflows/  # CI/CD
├── functions.php       # Main functions
├── header.php          # Wrapper → templates/partials/header.php
├── footer.php          # Wrapper → templates/partials/footer.php
└── style.css          # Theme stylesheet
```

## Common Patterns

### Adding a New Feature
1. Create file in `includes/features/feature-{name}.php`
2. Add `require_once` in `functions.php`
3. Log your changes in this file

### Adding a New Page Template
1. Create file in `templates/pages/page-{name}.php`
2. If using images, reference as `assets/images/...`
3. Log your changes in this file

### Deployment
- Manual only via GitHub Actions
- Go to Actions → Deploy to WordPress → Run workflow
- Requires secrets to be configured in GitHub

---

## Active Considerations

*None currently - add here if there are ongoing issues or things to watch out for*

