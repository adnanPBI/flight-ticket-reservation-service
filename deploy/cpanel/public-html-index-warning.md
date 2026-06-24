# cPanel document root warning

Point the temporary subdomain document root to:

```txt
/home/CPANEL_USER/ota/public
```

Do **not** copy Laravel's full project into `public_html`. Only the `public/` directory should be web-accessible.

If cPanel forces `public_html`, put the Laravel app outside web root and update `public/index.php` paths carefully. The safer route is a temporary subdomain with document root set to the Laravel `public` directory.
