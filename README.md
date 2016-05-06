Based on https://github.com/opauth/live

Opauth-Outlook
=============
[Opauth][1] strategy for Outlook authentication.

Implemented based on https://msdn.microsoft.com/en-us/library/azure/dn645542.aspx

Getting started
----------------
1. Install Opauth-Outlook:

   Using git:
   ```bash
   cd path_to_opauth/Strategy
   git clone https://github.com/t1mmen/opauth-outlook.git outlook
   ```

  Or, using [Composer](https://getcomposer.org/), just add this to your `composer.json`:

   ```bash
   {
       "require": {
           "t1mmen/opauth-outlook": "*"
       }
   }
   ```
   Then run `composer install`.


2. Create Outlook application at https://developer.outlook.com/apps/new

3. Configure Opauth-Outlook strategy with at least `Client ID` and `Client Secret`.

4. Direct user to `http://path_to_opauth/outlook` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Outlook' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET',
)
```

License
---------
Opauth-Outlook is MIT Licensed
Copyright © 2016 Timm Stokke (http://timm.stokke.me)

[1]: https://github.com/opauth/opauth
