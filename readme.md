# What is this?
A Command line tool, built for uptobox.com

>
> **NOTE**
>   This tool is built for my personal use only, but it has the most required
>   features, like login to your uptobox account, copying files from other
>   account, renaming files, and much more.
>
>   Feel free to adjust it for your need, and send me a PR :)
>

# Usage:
### Before you start
```sh
$ ./uptobox env:create
```
This command will create `.env` file on the root of the application, please
open it and fill in your uptobox username and password.
    
    UPTOBOX_USERNAME=
    UPTOBOX_PASSWORD=

### Command line:
```sh
$ ./uptobox <uptobox-link> --json
```

`--json` is not required, but in case you want to use the tool from other
programs, and you need to get the output as json, then pass this flag.

This command will copy the file from the given link to your account, and return
a direct download link, and a stream if it's available.

The returned data looks like this:

```php
'source_link' => '',       // link you provided
'new_link' => '',          // link to the file copied to your account
'para_title' => '',        // original title of the file (complete)
'name' => '',              // original name of the file
'size' => '',              // size
'add_to_my_account' => '', // file is added to your account
'slug' => '',              // new name of the file in your account (hashed)
'stream' => '',            // stream link
'direct' => '',            // direct link to your file
```
