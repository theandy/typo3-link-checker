# TYPO3 Navigation Link Checker

A small PHP CLI tool that scans TYPO3 websites for invalid navigation
links marked in the HTML with:

```{=html}
<!-- navigation-link-href:'' -->
```
The tool crawls all pages of each TYPO3 site and language, detects empty
navigation links, optionally flushes the TYPO3 cache, and sends a
notification email if problems remain.

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

------------------------------------------------------------------------

# Requirements

-   PHP 8.1 or higher
-   Composer
-   Access to the TYPO3 installation
-   SMTP account for sending emails

------------------------------------------------------------------------

# Installation

Clone the repository:

``` bash
git clone https://github.com/YOUR-REPOSITORY/typo3-link-checker.git
cd typo3-link-checker
```

Install dependencies:

``` bash
composer install
```

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

# Log Configuration

    'overwrite' => true

true → overwrite log file on every run (recommended for debugging)

false → append to existing log

Log file example:

    logs/link-checker.log

------------------------------------------------------------------------

# Usage

Run manually:

``` bash
php bin/check-links
```

or

``` bash
/usr/bin/php8.3 bin/check-links
```

Example output:

    TYPO3 Navigation Link Checker started
    Checking site: https://example.com
    Found 120 pages
    Navigation links found: 850
    Invalid navigation links: 2
    Flushing TYPO3 cache
    Rechecking pages
    Mail successfully sent
    Finished

------------------------------------------------------------------------

# Cronjob Example

Run every 30 minutes:

``` bash
*/30 * * * * /usr/bin/php /path/to/typo3-link-checker/bin/check-links
```

------------------------------------------------------------------------

# How the Tool Works

1.  Reads TYPO3 sites from `config/sites/`
2.  Reads page tree from database
3.  Builds URLs per site and language
4.  Crawls each page
5.  Searches for

```{=html}
<!-- -->
```
    <!-- navigation-link-href:'' -->

6.  Counts navigation markers
7.  If empty links are found:
    -   flush TYPO3 cache
    -   recheck pages
    -   send email if issue remains

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

------------------------------------------------------------------------

# Security

Do not commit real credentials.

Recommended:

    config/config.php

Add to `.gitignore`.

Instead commit:

    config/config.example.php

------------------------------------------------------------------------

# License

MIT
