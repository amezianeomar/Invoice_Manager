# InfinityFree Deployment Strategy: Invoice Manager

Moving from a decoupled architecture (Vercel Frontend + AlwaysData Backend) to a **unified single-domain architecture** on InfinityFree is the absolute best move for this project. 

Because InfinityFree will host *both* your React frontend and your PHP backend on the exact same domain (e.g., `ameziane.infinityfreeapp.com`), **all CORS errors and Third-Party Cookie blocks will instantly vanish.** The browser will natively trust the session cookie for PDF downloads.

Here is the step-by-step technical strategy to migrate the project:

## Phase 1: Clean Up the Codebase (Revert Hacks)
Since we no longer have to fight CORS, we can simplify the code back to its cleanest state:

1. **Dashboard.jsx (Frontend):** 
   - Revert the `handleViewPDF` fetch logic back to standard `<a>` hyperlink tags. 
   - The link will simply be `<a target="_blank" href="/generate-pdf.php?id=...">`.
2. **generate-pdf.php (Backend):** 
   - Re-enable the `$auth->requireAuth();` security check. Sessions will work flawlessly now.
   - (Optional) You can remove the complex CORS headers (`Access-Control-...`) at the top of the file.
3. **App.jsx & main.jsx (Frontend):** 
   - Ensure `axios.defaults.baseURL` is simply set to `'/api'`. No need for Absolute URLs.

## Phase 2: Build the React Frontend
InfinityFree does not run Node.js, so we must compile the frontend into static HTML, CSS, and JS files.
1. Open your terminal in the `frontend` folder.
2. Run `npm install` (if not already done).
3. Run `npm run build`.
4. This will generate a `dist/` directory containing `index.html` and an `assets/` folder. This is your completely production-ready frontend.

## Phase 3: Prepare the Database
1. Go to your InfinityFree Control Panel.
2. Create a new MySQL database.
3. Open PhpMyAdmin in InfinityFree and import your local `database.sql` file.
4. Note the newly generated credentials: *MySQL Host, MySQL User, MySQL Password, and Database Name*.
5. Update your `config/database.php` file with these new InfinityFree credentials.

## Phase 4: Construct the Production File Structure
You need to combine both projects into a single folder structure before uploading via FTP (FileZilla). Everything must go inside the `htdocs/` folder on InfinityFree.

Your local combined folder should look exactly like this:
```text
htdocs/
├── index.html            <-- (Copied from frontend/dist/)
├── vite.svg              <-- (Copied from frontend/dist/)
├── assets/               <-- (Copied from frontend/dist/)
├── api/                  <-- (Copied from root/api/)
├── classes/              <-- (Copied from root/classes/)
├── config/               <-- (Copied from root/config/, ensure database.php is updated)
├── auth.php              <-- (Copied from root/)
├── generate-pdf.php      <-- (Copied from root/)
└── .htaccess             <-- (NEW FILE: See Phase 5)
```

## Phase 5: Create the `.htaccess` File (CRITICAL)
Because React uses its own router (React Router), hitting a URL like `yoursite.com/login` directly will cause a 404 error on standard Apache servers like InfinityFree. 

You must create a `.htaccess` file in the root of your `htdocs` folder to tell the server to route frontend pages to `index.html`, **except** when requesting `/api/` or `.php` files.

**Create `.htaccess` with this exact content:**
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # Allow direct access to API folder and PHP scripts
  RewriteRule ^api/ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} !\.php$
  
  # Forward everything else to React's index.html
  RewriteRule . /index.html [L]
</IfModule>
```

## Phase 6: FTP Upload and Final Testing
1. Connect to InfinityFree using FileZilla.
2. Open the `htdocs` folder.
3. Delete any default files placed there by InfinityFree (like `index2.html`).
4. Upload all the contents from your combined folder (from Phase 4, including the `.htaccess`) into `htdocs`.
5. Visit your website. The React UI will load, the PHP API will respond, and clicking "Download PDF" will open natively and securely via a shared session cookie!
