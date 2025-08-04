<?php
function getOfficeDisplayName($code) {
    $map = [
        'dictbulacan' => 'Provincial Office Bulacan',
        'dictaurora' => 'Provincial Office Aurora',
        'dictbataan' => 'Provincial Office Bataan',
        'dictpampanga' => 'Provincial Office Pampanga',
        'dicttarlac' => 'Provincial Office Tarlac',
        'dictzambales' => 'Provincial Office Zambales',
        'dictothers' => 'Provincial Office Others',
        'dictne' => 'Provincial Office Nueva Ecija',
        'dictnuevaecija' => 'Provincial Office Nueva Ecija',
        'maindoc' => 'DICT Region 3 Office',
        'rdictpampanga' => 'Provincial Office Pampanga',
        'rdicttarlac' => 'Provincial Office Tarlac',
        'rdictbataan' => 'Provincial Office Bataan',
        'rdictbulacan' => 'Provincial Office Bulacan',
        'rdictaurora' => 'Provincial Office Aurora',
        'rdictzambales' => 'Provincial Office Zambales',
        'rdictnuevaecija' => 'Provincial Office Nueva Ecija',
        'rdictne' => 'Provincial Office Nueva Ecija',
        'rmaindoc' => 'DICT Region 3 Office'
    ];

    if (!$code) return '';
    $lower = strtolower($code);
    return $map[$lower] ?? $code;
}
