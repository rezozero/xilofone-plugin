# rezozero/xilofone-plugin

Fetch translations files from xilofone.rezo-zero.com and update them in your PHP project.

### Install

```shell
composer require --dev rezozero/xilofone-plugin
```

### Configuration

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
            "rezozero/xilofone-plugin": true,
        }
    }
}
```

Then add your secret credentials in your project `.env.local` file:

```dotenv
XILOFONE_PLUGIN_USERNAME=username
XILOFONE_PLUGIN_PASSWORD=password
```

