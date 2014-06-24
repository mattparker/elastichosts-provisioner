# ElasticHosts provisioner

## Introduction

This is a small php script that provisions multiple drives and servers on the [ElasticHosts](http://www.elastichosts.co.uk)
cloud.  You provide a .json file describing the servers and drives you want, and they should all just appear.

This is not a full client for the ElasticHosts API: it's only intended for initial provisioning.  It assumes
that none of the servers exist already.  There will soon be an ansible inventory output from this, so I'll be able
to recreate the entire environment programatically: this bit will make the servers, then ansible will set them up.

You need php 5.4 or above on the machine you're running this on.

This is version 0.2

I should probably say there is no affiliation with ElasticHosts and while they know about this there's no
endorsement or anything.


## Current features

At the moment you can:

 - create drives
 - set an image for a drive (from the set ElasticHosts provides)
 - create a server with up to 4 drives
 - set a server to avoid sharing hardware with any others
 - write out a simple ansible .yml inventory file for the servers you've just created


## CLI options

    -i     Path to inventory file.  If not given, will try and use ./server-inventory.json
    -c     Path to credentials file.  If not given, will try and use ./set-eh-credentials.php
    -p     Provider - where you want servers to appear. This is ignored at the moment.
    -o     Path to output file.  If not given, will use ./servers-created.yml
    -h     Show this help and exit

See below for more on what these mean.


## Usage

Firstly, you need the small elastichosts.sh script [available here](http://elastichosts.co.uk/support/api/) - although
it's also in this repo right now.

You'll need your credentials in a file 'set-eh-credentials.php' in the root directory.  There's a test version there
currently: you'll need to get your API key and secret from the ElasticHosts control panel.  The credentials file needs
this in it:

```php
<?php
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
                "boot": "ide:0:0",
                "nic:0:model": "e1000",
                "nic:0:dhcp": "auto",

                "nic:1:model": "e1000",

                "vnc": "auto",
                "password": "xxxxxxxx",

                "persistent": "true",

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
                        "size": 2000000000,
                        "ssd": true
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
                        "image": "UBUNTU_1204"
                    }
                ],
                "avoid": ["app1"]
            }
        ],

        "db": [
            {
                "name": "db1",
                "cpu": 1000,
                "mem": 2048
                "drives": [
                    {
                        "name": "db1bootdrive"
                        "size": "100000000000",
                        "image": "WIN_WEB_2008_SQL"
                    }
                ],
                "avoid": ["app1", "app2"]
            }
        ]
    },

    "vlan": "name_of_vlan_to_create",

    "meta": {
        "server-build-version": "0.1",
        "author": "Matt Parker"
    }
}
```

Servers and drives have a fair few more config options: I'll write them up but they're mostly the same
as the [ElasticHosts API](http://elastichosts.co.uk/support/api/).

With this example, roughly what'll happen (in this order):

1. Create a VLAN
2. Create drive app1bootdrive.
3. Create drive app1data (as an SSD disk)
4. Poll every few seconds until the imaging is complete on `app1bootdrive`.
5. Create server `app1`, with two drives attached (and `app1bootdrive` as boot drive) and attached to the VLAN.
    It will be attached to the VLAN because a) we created one using the `vlan` item, and b) we specified a model
    for the second NIC card.  A couple of other gotchas: the VNC password cannot be longer than 8 characters.
    And if you don't specify `"persistent": "true"` the server will disappear if you shut it down.
6. Create drive `app2bootdrive`, requesting that it avoid sharing hardware with server `app1`, or the drives attached
    to `app1` (ie `app1bootdrive` and `app1data`)
7. Poll every few seconds until the imaging is complete on `app2bootdrive`.
8. Create server `app2`, with drive `app2bootdrive` but not linked to the VLAN and not persistent.  Again it will request
    that the server does not share any hardware with `app1` or the drives linked to `app1`.
9. Create drive `db1bootdrive`, avoiding all hardware involved with `app1` and `app2`.
10. Wait until the Windows Web server 2008 + SQL imaging is done.
11. Create server `db`, again avoiding `app1` and `app2` hardware.
12. Write out the .yml inventory file for use with Ansible.


This will all happen in order, so your 'avoid' statements need to be down the list (i.e. it won't work to try and
tell `app1` to avoid `app2`).  Note that avoid is an array, so a third app node could avoid app1 and app2.

Then assuming all your files have default names:

```bash
$> ./build-eh-servers.php
```

You'll see logging output of what's happening and what the commands being run are.


### Drive images available

ElasticHosts provides some drive images that you can use.  Available ones are:

- CENTOS_65
- DEBIAN_74
- UBUNTU_1204
- UBUNTU_1310
- WIN_WEB_2008
- WIN_WEB_2008_SQL
- WIN_2008
- WIN_2008_SQL
- WIN_2012
- WIN_2012_SQL

i.e. pass one of these values in as "image" to the drive spec in the inventory file.  The Windows ones of these
will incur additional licensing costs - see the ElasticHosts website for more on all this.




## Output

The script will generate a yml file (intended for use with ansible).  It will add a little header, and then
group the servers using the top level names.  It will also add meta data specified in the inventory file
to the header.

So using the inventory file above will give you output like this:

```yml
#
# Ansible inventory file. Generated by ElasticHosts provisioner 24/06/2014 10:44:44
#
# server-build-version : 0.1
# author : Matt Parker
[app]
app1 ansible_ssh_host=91.123.456.789
app2 ansible_ssh_host=91.123.456.790


[db]
db1 ansible_ssh_host=91.123.456.791

```

The inventory file is called `servers-created.yml` and will overwrite anything there already.  You can change this using
the `-o` option.



## OK, I've got some servers, what next?

You'll probably need to connect via VNC to set up a initial user account to connect with over SSH.  I'll then hand over
to ansible to set the servers up as needed.



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

 - [x] Output writer to take servers after they're set up and write IPs etc into an ansible inventory file
 - [x] Some more error checking
 - [x] Allow command line option to set credentials file
 - [x] Allow command line option to set inventory file
 - [x] Ability to create a VLAN
 - [ ] Could refactor response parsing out into separate classes
 - [ ] Look at a VirtualBox implementation for testing (ie to create servers in VirtualBox locally from the same inventory file).


## License

[MIT](LICENSE)


Copyright Matt Parker, Lamplight Database Systems 2014

www.lamplightdb.co.uk

matt@lamplightdb.co.uk
