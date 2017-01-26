# phphgadmin

An updated version of the original PhpHgAdmin by Josh Carrier.

--------------------------------

```
phpHgAdmin - Mercurial Repository Manager for PHP
for
Mercurial Shared-server (hgwebdir.cgi) Deployments
Mercurial 1.3.1-compliant
PHP 5.2-compliant
CodeIgniter 1.7.2-compliant
==============================
Provides an interface to edit the hgweb.config and hgrc files of
Mercurial repositories hosted with the standard hgwebdir.cgi web script.

Included are their relative installation directories to the top domain. This application assumes '/admin' will
be used to access the interface. To function properly, no Mercurial repository should have the name 'admin' prior to this installation.

Pre-requisites:
* server supports Mercurial (visit web site for more information)
* server supports PHP
* all files and folders are writable by the server where:
	* folders where Mercurial holds it's configuration and repositories
	* ./lock directory, where this application exercises optimistic locking and has a small amount of scratch space

Configuration files:
Must revise and edit these configuration files before installation is complete. In /admin/application/config/:
	* phphgadmin.php - Mercurial-related defaults
						
This system was designed for use with the quick Hg installation guide available at:
http://redirect.joshjcarrier.com/?r=hgwebinstall

More information available on the blog.
==============================
Author: Josh Carrier <josh@joshjcarrier.com>
Web: http://redirect.joshjcarrier.com/?r=hgphp

Changelog:
1.1.201023
- rebranded to phpHgAdmin
- framework revised
- support for multiple hgweb.config
- much more customizable and flexible installation
- multiple bug fixes
Upgrade warning: 
	Fresh install recommended due to significant changes. hgweb.config [paths] section is now modified in preference to [collections].
	You should manually repair the hgweb.config file. An entry:
		[collections]
		/path/to/repo/name = name
		should now be referred to as
		[paths]
		name = /path/to/repo/name 

1.0.20100519
- initial release


-- Disclaimer --
This application contains code written by other developers
- EllisLab, Inc. (CodeIgniter)
- jQuery Project (jQuery & jQuery UI)
- Selenic (Mercurial theme stylesheets and logo)
This application is provided as-is, without warranty or held liabilities of any kind 
and by using this software you are accepting all subjected licence agreements. 
Do not modify the licensing and credentials attributed to the aforementioned
developers or the author of this application.
:)
```