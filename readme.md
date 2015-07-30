#Hotfix

Version **1.0.0**

> This module allow older PrestaShop version to be upgraded only for small security fixes.

##Compatibility

 - PrestaShop 1.4 _(W.I.P.)_
 - PrestaShop 1.5 _(W.I.P.)_
 - PrestaShop 1.6

##How to create a log

 - Set a PrestaShop folder with all the patches but not the last one
 - Set anoter PrestaShop folder with all the patches.
 - Run this command line :

```bash
diff --unidirectional-new-file --context=5 -r -x .git -x cache PrestaShop/ PrestaShopFixed/ > diff.patch
```

##Changelog

###0.1

 - Initial version.
