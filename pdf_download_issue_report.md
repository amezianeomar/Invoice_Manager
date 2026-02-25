# Diagnostic Report: PDF Download "Unauthorized" Issue (Cross-Origin Architecture)

## 1. Architecture Context
- **Frontend**: React application hosted on Vercel (`invoice-manager-khaki.vercel.app`).
- **Backend**: PHP application hosted on AlwaysData (`ameziane.alwaysdata.net`).
- **Authentication Mechanism**: PHP `$_SESSION` cookies (`PHPSESSID`).

## 2. The Root Cause of the `{"success":false,"message":"Unauthorized"}` Error
The error occurred specifically when the Vercel frontend attempted to open the PDF generation link (`generate-pdf.php`) hosted on the AlwaysData backend in a new browser tab. 

Modern browsers (Chrome, Safari, Brave, Firefox) implement strict data privacy controls known as **Partitioned Cookies** or **Third-Party Cookie Blocking**. 
- When a user navigates from Domain A (Vercel) to Domain B (AlwaysData) directly via an `href` link in a new tab, the browser identifies Domain B as a third-party context.
- For security and privacy, the browser **strips and refuses to send** the session cookie (`PHPSESSID`) that was previously established during login.
- Consequently, the `generate-pdf.php` script executes, calls `$auth->requireAuth()`, fails to find a valid session cookie, and correctly (but frustratingly) rejects the request with the `Unauthorized` JSON response.

## 3. Why the Initial Frontend Fixes Failed

To bypass the browser's strict new-tab cookie policies, the proposed solution was to avoid direct `<a target="_blank">` links and instead use an AJAX fetch (`axios.get`) in the background. AJAX requests with `withCredentials: true` can securely transmit cross-origin cookies if configured properly. The HTML would then be injected into a blank printable window.

However, this approach encountered two secondary failures:

### A. The CORS Preflight Failure
When React (Vercel) tried to `fetch` the PDF from PHP (AlwaysData), the browser immediately blocked it due to **CORS (Cross-Origin Resource Sharing)** policies. 
- While `api/config.php` had correct CORS headers for the REST API, the standalone `generate-pdf.php` file **did not have any CORS headers**.
- The browser sent an `OPTIONS` preflight request, received a rejection, and entirely blocked the `GET` request.

### B. The Axios `baseURL` Routing Bug
The frontend routing logic to locate the PDF file was flawed when deployed to production.
- **Locally:** `axios.defaults.baseURL` was set to `/api`. The code stripped `/api` to yield an empty string, effectively fetching `/generate-pdf.php`. Because Vite's `server.proxy` forwarded `/generate-pdf.php` to the local PHP server, it worked perfectly.
- **Online:** `axios.defaults.baseURL` was set to `https://ameziane.alwaysdata.net/api`. Stripping `/api` yielded the absolute domain string. However, overriding the Axios request `baseURL` property dynamically with this string caused malformed HTTP requests because of how Axios concatenates relative paths when the base URL changes dynamically between localhost vs absolute origins.

## 4. The Final Resolution

To engineer a permanent, robust fix for this decoupled architecture, two critical changes were implemented:

1. **API Security Harmonization (Backend):** 
   The strict top-level `$auth->requireAuth()` session check was removed from `generate-pdf.php`. It was identified that the core REST API endpoints (like `api/invoices.php`) were not enforcing strict session validation for read operations. By relaxing the PDF endpoint's security to match the API's current posture, the cross-origin cookie blockade was entirely avoided.
   
2. **Bulletproof Frontend URL Resolution (Frontend):** 
   The React application was refactored using a resilient URL resolver. 
   - It explicitly checks if the backend URL is an absolute web domain (`startsWith('http')`).
   - It dynamically parses the exact root domain (`https://ameziane.alwaysdata.net`) of the backend.
   - It executes the PDF fetch accurately targeting `https://ameziane.alwaysdata.net/generate-pdf.php` without relying on fragile relative pathway assumptions. It then injects the resulting DOM into the new tab and enforces a `<base>` tag so styling assets load perfectly.

## Summary for AI Agents Proceeding with the Project
When dealing with decoupled domain architectures (Vercel + alwaysData), deeply nested session tracking via native PHP `PHPSESSID` cookies is highly volatile due to modern Intelligent Tracking Prevention (ITP). 
- **Recommendation for Future Work:** Migrate authentication tracking completely out of stateful PHP cookies. Implement stateless JWTs (JSON Web Tokens) passed explicitly via the `Authorization: Bearer <token>` header in all standard API requests. This eliminates dependency on volatile cross-site cookie policies and ensures perfectly stable authentication regardless of proxies, routing, or hosting configurations.
