# ElasticHosts provisioner

## Introduction

This is a small php script that provisions multiple drives and servers on the (ElasticHosts) [http://www.elastichosts.co.uk]
cloud.  You provide a .json file describing the servers and drives you want, and they should all just appear.

This is not a full client for the ElasticHosts API: it's only intended for initial provisioning.  It assumes
that none of the servers exist already.  There will soon be an ansible inventory output from this, so I'll be able
to recreate the entire environment programatically: this bit will make the servers, then ansible will set them up.

You need php 5.4 or above on the machine you're running this on.

This is version 0.1


## Current features

At the moment you can:

 - create drives
 - set an image for a drive (from the set ElasticHosts provides)
 - create a server with up to 4 drives
 - set a server to avoid sharing hardware with any others


## Usage

Firstly, you need the small elastichosts.sh script (available here) [http://elastichosts.co.uk/support/api/].

You'll need your credentials in a file 'set-eh-credentials.php' in the root directory.  There's a test version there
currently: you'll need to get your API key and secret from the ElasticHosts control panel.  The credentials file needs
this in it:

```php
putenv('EHAUTH=<user uuid>:<secret API key>');
putenv('EHURI=https://api-lon-p.elastichosts.com/');
```

The description of the servers you want should be in the file `server-inventory.json`, also in the project root, and it
should look something like this:

```json
{
    "servers": {

        "app": [
            {
                "name": "app1",
                "cpu": 500,
                "mem": 256,
                "drives": [
                    {
                        "name": "app1bootdrive",
                        "boot": true,
                        "size": 8000000000,
                        "image": "DEBIAN_74"
                    },
                    {
                        "name": "app1data",
                        "boot": false,
                        "size": 2000000000
                    }
                ]


            },
            {
                "name": "app2",
                "cpu": 1000,
                "mem": 2048
                "drives": [
                    {
                        "name": "app2bootdrive"
                        "size": "123456789",
                        "image": "WIN_WEB_2008_SQL"
                    }
                ],
                "avoid": "app1"
            }
        ],

        "db": [

        ]
    }
}
```

Servers and drives have a fair few more config options: I'll write them up but they're mostly the same
as the ElasticHosts API.

In this example, "avoid": "app1" will request that the drives and server for app2 are on different hardware
to that used by `app1` (using the server name).  For each server, all drives are created first, and imaging happens.
 Everything will wait until imaged drives are ready (it polls until they're done).  Then the server is created.

This will all happen in order, so your 'avoid' statements need to be down the list (i.e. it won't work to try and
tell `app1` to avoid `app2`).


## Tests

There are unit tests in the `tests/` folder.  `phpunit` is the only composer dependency.  However there's some short array
syntax (so you need php 5.4).

There's also an extremely simple webserver that mocks part of the ElasticHosts API to run full tests.  Create a
testing credentials file, that looks something like this:

```php
putenv('EHAUTH=matt:hello');
putenv('EHURI=http://localhost:8000');
```

and then start the built-in server:

```bash
$ php -S localhost:8000 -t php/tests/ehendpoint/
```

You can then run `build-eh-servers.php` and it'll point at the mocked, local webserver.  This will give passable
imitations of the ElasticHosts endpoints that are mocked, although it ignores your inputs and is stateless.  So
the Server information returned when you 'create' a server won't have the memory, cpu etc that you specify.  There's
also no error checking in it.  In other words, it's pretty basic, but you can see what's going on.


## Todo

In rough order of priority for me:

- Output writer to take servers after they're set up and write IPs etc into an ansible inventory file
- Some more error checking
- Allow command line option to set credentials file
- Allow command line option to set inventory file
- Could refactor response parsing out into separate classes
- Look at a VirtualBox implementation for testing (ie to create servers in VirtualBox locally from the same inventory file).

## License

MIT.


Copyright Matt Parker, Lamplight Database Systems 2014
www.lamplightdb.co.uk
matt@lamplightdb.co.uk
