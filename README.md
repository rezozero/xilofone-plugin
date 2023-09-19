# rezozero/xilofone-plugin

Fetch XLIFF translations files from xilofone.rezo-zero.com and update them in your PHP project.

Plugin will download translations files each time you run `composer update`. Or you can run it manually with:

```shell
composer xilofone:fetch-files
```

### Install

```shell
composer require --dev rezozero/xilofone-plugin
```

### Configuration

Add the following configuration in your `composer.json` file:

```json
{
    "extra": {
        "xilofone": {
            "file_id": "30",
            "destination_folder": "translations"
        }
    },
    "config": {
        "allow-plugins": {
            "rezozero/xilofone-plugin": true
        }
    }
}
```

Then add your secret credentials in your project `.env.local` file:

```dotenv
XILOFONE_PLUGIN_USERNAME=username
XILOFONE_PLUGIN_PASSWORD=password
```

