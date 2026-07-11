# Barnahus site tools workflow

This repo contains the custom WordPress plugin for barnahus.eu.

The deployable plugin is the inner `barnahus-site-tools/` folder. The outer folder is the Git checkout and should not be uploaded as the plugin itself.

## Everyday editing

1. Ask Codex for the change you want.
2. Review the changed files.
3. Run the checks:

   ```sh
   ./scripts/check.sh
   ```

4. Build a WordPress-ready zip:

   ```sh
   ./scripts/build-zip.sh
   ```

5. Upload the newest zip from `dist/` to WordPress.

The build only uses committed source. It stops if the deployable plug-in folder contains uncommitted or untracked files, which prevents local drafts from entering a release accidentally. The same commit produces the same release checksum on repeated builds.

## Recommended WordPress push

For the safest first version of this workflow, deploy manually through WordPress:

1. In WordPress admin, go to **Plugins > Add New Plugin > Upload Plugin**.
2. Upload `dist/barnahus-site-tools.zip`.
3. WordPress will ask whether to replace the existing plugin. Confirm.
4. Visit the affected page or feature and make sure it looks right.

This keeps the live-site step deliberate while still letting Codex edit, check, and package the plugin.

## FTP deployment

If you want to deploy by FTP instead of uploading the zip manually:

1. Copy `.env.example` to `.env`.
2. Fill in your FTP host, username, password, and remote plugin path.
3. Run a harmless FTP write test:

   ```sh
   ./scripts/test-ftp.sh
   ```

4. Deploy the plugin:

   ```sh
   ./scripts/deploy-ftp.sh
   ```

The FTP test uploads a temporary `codex-deploy-test.txt` file, confirms it exists, and removes it. The FTP deployment then:

1. Runs all checks.
2. Builds from the current commit.
3. Downloads the live plug-in into `../rollback-archives/`.
4. Stops before upload if that rollback copy is incomplete.
5. Mirrors the approved release exactly, including removal of obsolete files.

It does not upload the outer Git checkout, `dist/`, or your `.env` file. BQS publishing source belongs in the separate `barnahus-publishing` project, and the checks stop if a copy appears inside this plug-in.

Prefer `ftps` in `.env` if your host supports it. Plain `ftp` sends credentials without encryption.

## GitHub flow

Use GitHub as the source of truth:

1. Make edits locally with Codex.
2. Run `./scripts/check.sh`.
3. Bump the version for meaningful releases:

   ```sh
   ./scripts/bump-version.sh 1.0.1
   ```

4. Commit the changes with a clear message.
5. Push to GitHub.
6. Tag the release:

   ```sh
   git tag v1.0.1
   git push origin v1.0.1
   ```

7. GitHub Actions builds the release zip and attaches it to the GitHub Release.
8. Deploy the zip to WordPress only when ready.

Suggested branch naming:

```text
feature/short-description
fix/short-description
```

## Optional direct deployment later

Once the manual or FTP upload flow feels comfortable, this repo can be extended with one of these direct deploy options:

- WP-CLI plugin install from the generated zip
- GitHub Actions deploy on tagged releases

Before adding direct deploy, confirm:

- hosting provider
- whether SFTP, FTPS, or SSH is available
- WordPress path on the server
- whether there is a staging site

## Safety checklist

Before deploying:

- `./scripts/check.sh` passes or reports only missing local tooling.
- `./scripts/build-zip.sh` creates `dist/barnahus-site-tools.zip`.
- `./scripts/build-zip.sh` creates a versioned zip such as `dist/barnahus-site-tools-v1.0.1.zip`.
- The zip contains `barnahus-site-tools/barnahus-site-tools.php` at the top level inside the archive.
- `VERSION`, `barnahus-site-tools/barnahus-site-tools.php`, and `barnahus-site-tools/README.md` all show the same version.
- The change has been tested on the affected page after upload.
