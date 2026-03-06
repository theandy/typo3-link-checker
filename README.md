# TYPO3 Navigation Link Checker

A PHP CLI tool that scans TYPO3 websites for invalid navigation links
rendered in HTML comments such as:

```{=html}
<!-- navigation-link-href:'' -->
```
The tool crawls all pages of every TYPO3 site and language, detects
empty navigation links, optionally flushes the TYPO3 cache, and sends a
notification email if the issue remains.

------------------------------------------------------------------------

# Features

-   Crawls all TYPO3 sites defined in `config/sites/`
-   Supports multiple languages
-   Detects navigation markers in HTML comments
-   Counts navigation links and empty navigation links
-   Flushes TYPO3 cache when errors are detected
-   Rechecks affected pages after cache flush
-   Sends notification emails
-   Writes detailed logs
-   CLI compatible (cronjobs)
-   Progress bar during crawling
-   Colored console output for warnings and errors

------------------------------------------------------------------------

# Requirements

-   PHP **8.1 or higher**
-   Composer
-   Access to the TYPO3 installation
-   SMTP account for sending emails

Required PHP extensions:

-   curl
-   json
-   mbstring
-   openssl
-   dom
-   libxml

------------------------------------------------------------------------

# Installation

Clone the repository:

``` bash
git clone https://github.com/theandy/typo3-link-checker.git
cd typo3-link-checker
```

Install dependencies:

``` bash
composer install
```

Optimize autoload (recommended):

``` bash
composer dump-autoload -o
```

Make the CLI script executable:

``` bash
chmod +x bin/check-links
```

------------------------------------------------------------------------

# Directory Setup

Create the log directory if it does not exist:

``` bash
mkdir logs
chmod 775 logs
```

The application will write logs to:

    logs/link-checker.log

------------------------------------------------------------------------

# Configuration

Edit:

    config/config.php

Example configuration:

``` php
return [

    'database' => [
        'host' => 'localhost',
        'dbname' => 'typo3_db',
        'user' => 'typo3_user',
        'password' => 'secret'
    ],

    'typo3' => [
        'root_path' => '/var/www/project/'
    ],

    'mail' => [
        'to' => 'admin@example.com',

        'smtp' => [
            'host' => 'mail.example.com',
            'port' => 587,
            'username' => 'monitor@example.com',
            'password' => 'PASSWORD',
            'encryption' => 'tls'
        ],

        'from' => [
            'address' => 'monitor@example.com',
            'name' => 'TYPO3 Monitor'
        ]
    ],

    'log' => [
        'file' => __DIR__ . '/../logs/link-checker.log',
        'overwrite' => true
    ]

];
```

------------------------------------------------------------------------

# Navigation Marker Format

Valid navigation marker:

    <!-- navigation-link-href:'/products' -->

Invalid navigation marker (detected by the tool):

    <!-- navigation-link-href:'' -->

------------------------------------------------------------------------

# Usage

Run manually:

``` bash
php bin/check-links
```

or:

``` bash
/usr/bin/php8.3 bin/check-links
```

Example output:

    TYPO3 Navigation Link Checker started
    Checking site: https://example.com
    Found 120 pages
    120/120 [████████████████████████████] 100%
    Navigation links found: 850
    Invalid navigation links: 2
    Flushing TYPO3 cache
    Rechecking pages
    Mail successfully sent
    Finished

------------------------------------------------------------------------

# Cronjob Example

Run every hour:

``` bash
0 * * * * /usr/bin/php /path/to/typo3-link-checker/bin/check-links > /dev/null 2>&1
```

Run every 30 minutes:

``` bash
*/30 * * * * /usr/bin/php /path/to/typo3-link-checker/bin/check-links > /dev/null 2>&1
```

------------------------------------------------------------------------

# How the Tool Works

1.  Reads TYPO3 sites from `config/sites/`
2.  Reads page tree from the TYPO3 database
3.  Builds URLs per site and language
4.  Crawls each page using concurrent HTTP requests
5.  Searches the HTML source for navigation markers
6.  Counts navigation markers and empty navigation links
7.  If empty links are found:
    -   Flush TYPO3 cache
    -   Recheck affected pages
    -   Send email if issues remain

------------------------------------------------------------------------

# Project Structure

    bin/
        check-links

    config/
        config.php

    logs/

    src/
        Application.php
        Checker/
        Crawler/
        Infrastructure/
        Typo3/

    composer.json

------------------------------------------------------------------------

# Troubleshooting

## Script runs very slowly

Check:

-   Network latency
-   TYPO3 caching
-   Server resources

## Mail not sent

Verify:

-   SMTP credentials
-   Firewall rules
-   Log file output

------------------------------------------------------------------------

# Security

Do **not commit real credentials**.

Recommended:

    config/config.php

Add to `.gitignore`.

Instead commit:

    config/config.example.php

------------------------------------------------------------------------

# License

MIT
