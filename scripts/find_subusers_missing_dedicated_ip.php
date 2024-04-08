<?php
require(__DIR__.'/../bootstrap.php');

$sendgrid = new SendgridBuddy\SendgridBase;
$ips = $sendgrid->getParentAccountAssignedIPs();
$ips = array_map(function ($ip) {
    return $ip['ip'];
}, $ips);
$havingIPAccounts = [];
foreach ($ips as $ip) {
    $accounts = $sendgrid->getSubUsersOnAssignedIP(true, $ip);
    $havingIPAccounts = array_merge($havingIPAccounts, $accounts);
}
$allAccounts = $sendgrid->getSubUsers(true);
$missingIPAccounts = array_filter($allAccounts, function ($account) use ($havingIPAccounts) {
    return !in_array($account['id'], $havingIPAccounts);
});
$missingIPAccounts = array_merge([], $missingIPAccounts);

$fh = fopen(__DIR__ . '/../output/missingIPAccounts.csv', 'w');
fputcsv($fh, ['Username', 'Id', 'Email', 'Disabled']);
foreach ($missingIPAccounts as $missingIPAccount) {
    fputcsv($fh, [
        $missingIPAccount['username'],
        $missingIPAccount['id'],
        $missingIPAccount['email'],
        $missingIPAccount['disabled']
    ]);
}
