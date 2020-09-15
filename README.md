# How to set up free SSL certificates on GoDaddy hosting?

With this guide you will be able to set up free auto-renewing SSL ([Let’s Encrypt](https://letsencrypt.org)) certificates on GoDaddy hosting.

## 1. Setting up SSH

a. [A complete guide to configuring SSH](https://vlemon.com/BLOG/Hosting/enable-ssh-on-godaddy-shared-hosting)

b. if point (a) is not available:

* [Enable SSH for your Linux Hosting account](https://www.godaddy.com/help/enable-ssh-for-my-linux-hosting-account-16102)
* [Set up your SSH keys](https://www.godaddy.com/garage/how-to-configure-an-openssh-client-on-a-linux-server/)
* [Connect to your server with SSH (Secure Shell)](https://www.godaddy.com/help/connect-to-my-server-with-ssh-secure-shell-4943)

## 2. Connecting to hosting via SSH

```sh
ssh username@primary.host
```

* `username` — [Find your username for Linux Hosting](https://in.godaddy.com/help/find-my-ftp-username-for-linux-hosting-16100)
* `primary.host` — [Find your primary host on GoDaddy hosting](https://in.godaddy.com/help/find-my-gen-4-server-ip-address-24727)

## 3. Install acme.sh (Let's Encrypt client)

[Client repository](https://github.com/acmesh-official/acme.sh)

Run in shell:

```sh
curl https://get.acme.sh | sh
```

And re-login to ssh:

```sh
exit
```

Go to step 2. Then step 4.

## 4. Install post-renew hook

Download [post-renew-hook.php](post-renew-hook.php) file to your computer.

[Open GoDaddy file manager in your browser](https://in.godaddy.com/help/manage-files-in-my-linux-hosting-account-12426)

Copy `post-renew-hook.php` script file to the folder with acme.sh: `~/.acme.sh/`

## 5. Getting GoDaddy API Keys

As stated in the `acme.sh` documentation: [Use GoDaddy.com domain API to automatically issue cert](https://github.com/acmesh-official/acme.sh/wiki/dnsapi#4-use-godaddycom-domain-api-to-automatically-issue-cert)

You need to login to your GoDaddy account to get your API Key and Secret.

[https://developer.godaddy.com/keys/]

Create a Production key, instead of a Test key.

Execute the command in ssh substituting the your values:

```sh
export GD_Key="yourKey"
export GD_Secret="yourSecret"
```

## 6. Issuing SSL certificates

Now you can start issuing certificates. Specify your domain instead `example.com`. If you have subdomains, then you need to specify additionally `*.example.com` so that the certificate is applied to them as well.

```sh
acme.sh --issue --dns dns_gd -d example.com -d *.example.com --renew-hook "php ~/.acme.sh/post-renew-hook.php example.com"
```

Remember to replace the last `example.com` in `--renew-hook` to your domain.

*You can repeat this step for each domain on your hosting.*

## Congratulations

**Now you have free SSL certificates configured on your hosting!** Your sites are available via HTTPS protocol.
If you did everything according to the instructions, then the certificates will be updated automatically, without your participation. Isn't that cool?
