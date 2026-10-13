# Deployment

This project is a static website with a PHP-powered membership form. GitHub stores the source code, but **GitHub Pages does not run PHP**. To accept applications, use a web host that supports PHP and connect it to this GitHub repository.

## 1. Push the project to GitHub

1. Sign in to [GitHub](https://github.com/) and create a repository for the project (or use the repository you already created).
2. In the project folder, commit and push your files. For example, if your default branch is `main`:

   ```bash
   git add .
   git commit -m "Prepare Apex Sports Club for deployment"
   git push origin main
   ```

   If your branch has a different name, substitute it for `main`. Check that the repository contains `index.html`, the other site files, `process.php`, and the `assets` folder.

## 2. Deploy the working site (PHP hosting)

Choose a hosting provider that supports PHP and can deploy from a GitHub repository. In the provider's dashboard:

1. Create a PHP website and connect your GitHub account and this repository. If asked, select the branch you want to publish (usually `main`).
2. Set the website's document root to the project files' directory. The HTML pages and `process.php` are in the repository root.
3. Use PHP 8.1 or later with the `mbstring` extension enabled.
4. Enable HTTPS for the site.
5. Make sure PHP can write to the location used for application storage. `process.php` currently writes to `submissions.csv` in the project directory; some hosts replace or make this directory read-only during deployments. Use the host's persistent storage and keep application data out of the public web root before accepting real applications.
6. Visit the site's `index.html`, then submit a test application from the Join page. Confirm that the receipt page appears and that the submission is saved in the configured private, persistent storage.

If the host does not offer GitHub integration, use its Git deployment instructions or upload the project files through its file manager. Keep the source repository on GitHub as the version-controlled source of truth.

### Protect application data before launch

Application records contain personal information. Do not commit real submissions to GitHub or make the CSV downloadable from the public website. The repository currently tracks a header-only `submissions.csv`; before collecting real applications, stop tracking that file and add it to `.gitignore`, then configure the PHP handler and host to store the CSV outside the public web root (or deny public access to it). Keep that storage persistent across deployments and restrict access to authorized staff.

## 3. Optional: publish a static preview with GitHub Pages

Use this only if you want a static preview and do **not** need working membership submissions:

1. Open the repository on GitHub and go to **Settings → Pages**.
2. Under **Build and deployment**, choose **Deploy from a branch**.
3. Select the branch to publish and the `/ (root)` folder, then save.
4. Wait for the deployment to finish. GitHub shows the published URL in the Pages settings; for a project site it is usually `https://<your-github-username>.github.io/<repository-name>/`.

The HTML pages and assets can be displayed on Pages, but the membership form posts to `process.php`, which Pages cannot execute. The form therefore will not record applications there. Use PHP hosting above for a live site with a working form.

## Updating the deployed site

Make and test changes locally, then commit and push them to the deployment branch:

```bash
git add .
git commit -m "Describe the site update"
git push origin main
```

Replace `main` if your deployment branch has a different name. A host connected to GitHub will typically redeploy after each push; check its deployment dashboard if the update does not appear.
