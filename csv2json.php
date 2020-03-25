<?php
$data = [];
$handle = fopen("data.csv", "r");
while (($row = fgetcsv($handle, 999999, ',', '"')) !== false) {
	$data[] = $row;
}
fclose($handle);

// Remove starting text
$header = array_splice($data, 0, 3);

$languageMap = [ '', 'fi', 'en', 'tr', 'pl', 'ru', 'et', 'ar', 'so', 'ckb', 'ku-latn', 'fa', 'zh',
	'sq', 'vi', 'th', 'es', 'de', 'se', 'smn', 'sms', 'cs', 'pt-br', 'nb' ];

$translations = [];
foreach ($languageMap as $index => $code) {
	$authors = $header[1][$index];
	$authors = str_replace('Translators, write your names here!', '', $authors);
	$authors = str_replace('(proofreading)', '', $authors);
	$authors = trim($authors);
	if (!$authors) {
		continue;
	}

	$authors = array_map('trim', explode('/', $authors));
	$translations[$code]['@metadata']['authors'] = $authors;
}

$doc = '';
foreach ($data as $row) {
	if ($row[0] === '' && $row[1] !== '') {
		$doc = "Used in {$row[1]}";
		continue;
	}

	$key = $row[0];
	if (!preg_match('/^[a-z0-9-]+$/', $key)) {
		var_dump($row);
		continue;
	}

	$translations['qqq'][$key] = $doc;
	foreach ($languageMap as $index => $code) {
		$translation = $row[$index] ?? '';

		if ($code === '' || !$translation || $translation === 'You don\'t have to translate these') {
			continue;
		}

		$translation = trim($translation);

		$translations[$code][$key] = $translation;
	}

	if ($key === 'alt-9') {
		break;
	}
}

foreach ($translations as $code => $messages) {
	$out = json_encode(
		$messages,
		JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
	);

	$out = str_replace('    ', "\t", $out);

	file_put_contents("$code.json",$out);
}
