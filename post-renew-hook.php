<?php

/**
 * ACME Hook Post Renew
 * 
 * This script accepts one argument as a domain name. And installs a certificate for the domain and all its subdomains.
 * 
 * @author Ignat Awwit <ignatius.awwit@gmail.com>
 * @license MIT https://github.com/awwit/godaddy-free-https-guide/blob/main/LICENSE
 *
 * @link https://github.com/awwit/godaddy-free-https-guide
 */
class AcmeHookPostRenew {
  private static function endsWith(string $haystack, string $needle): bool {
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
  }

  private static function updateCert(string $domain, string $cert, string $key): void {
    $output = json_decode(shell_exec("uapi SSL install_ssl domain=\"{$domain}\" cert=\"{$cert}\" key=\"{$key}\" --output=jsonpretty"), true);

    if ($output['result']['errors']) {
      throw new Exception(json_encode($output, JSON_PRETTY_PRINT));
    }
  }

  public static function execute(string $domain, bool $verbose = FALSE): void {
    $dir = "{$_SERVER['HOME']}/.acme.sh/{$domain}";

    if (!is_dir($dir)) {
      throw new Exception("Data files for \"{$domain}\" domain not found.");
    }

    if ($verbose) { echo 'Deploying certs...', "\n"; }

    $output = json_decode(shell_exec('uapi DomainInfo list_domains --output=jsonpretty'), true);

    if ($output['result']['errors']) {
      throw new Exception(json_encode($output, JSON_PRETTY_PRINT));
    }

    $data = $output['result']['data'];

    $subdomains = $data['sub_domains'];

    if ($verbose) { echo "Update cert for {$domain}\n"; }

    $cert = urlencode(file_get_contents("{$dir}/{$domain}.cer"));
    $key = urlencode(file_get_contents("{$dir}/{$domain}.key"));

    self::updateCert($domain, $cert, $key);

    foreach ($subdomains as $subdomain) {
      if (self::endsWith($subdomain, $domain)) {
        if ($verbose) { echo "Update cert for {$subdomain}\n"; }

        self::updateCert($subdomain, $cert, $key);
      }
    }
  }
}

/**
 * Execute this script only if it is specified as initial and called from the command line.
 *
 * You can include this file in your script and manually call the `AcmeHookPostRenew::execute` function.
 */
if (php_sapi_name() === 'cli' && $argc >= 1 && realpath($argv[0]) === __FILE__) {
  if ($argc < 2) {
    echo 'Please enter a domain.', "\n";
    exit(1);
  }

  try {
    AcmeHookPostRenew::execute($argv[1], TRUE);

    echo 'Done!', "\n";
  } catch (Exception $exc) {
    echo $exc->getMessage(), "\n";
  }
}
