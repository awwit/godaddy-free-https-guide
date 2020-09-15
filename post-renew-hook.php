<?php

function endsWith($haystack, $needle) {
  return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

function updateCert($domain, $cert, $key) {
  $output = json_decode(shell_exec("uapi SSL install_ssl domain=\"{$domain}\" cert=\"{$cert}\" key=\"{$key}\" --output=jsonpretty"), true);

  if ($output['result']['errors']) {
    echo json_encode($output, JSON_PRETTY_PRINT)."\n";
    exit(1);
  }
}

if ($argc < 2) {
  echo "Please enter a domain.\n";
  exit(1);
}

$domain = $argv[1];

if (!is_dir("{$_SERVER['HOME']}/.acme.sh/{$domain}")) {
  echo "Data files for \"{$domain}\" domain not found.\n";
  exit(1);
}

echo 'Deploying certs...'."\n";

$output = json_decode(shell_exec('uapi DomainInfo list_domains --output=jsonpretty'), true);

if ($output['result']['errors']) {
  echo json_encode($output, JSON_PRETTY_PRINT)."\n";
  exit(1);
}

$data = $output['result']['data'];

$subdomains = $data['sub_domains'];

echo "Update cert for {$domain}\n";

$cert = urlencode(file_get_contents("{$_SERVER['HOME']}/.acme.sh/{$domain}/{$domain}.cer"));
$key = urlencode(file_get_contents("{$_SERVER['HOME']}/.acme.sh/{$domain}/{$domain}.key"));

updateCert($domain, $cert, $key);

foreach ($subdomains as $subdomain) {
  if (endsWith($subdomain, $domain)) {
    echo "Update cert for {$subdomain}\n";

    updateCert($subdomain, $cert, $key);
  }
}

echo 'Done!'."\n";
